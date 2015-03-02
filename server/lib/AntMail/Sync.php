<?php
 /**
 * Synchorinzes the imap email messages and saves into local storage
 * 
 * @category  AntMail
 * @package   IMAP
 * @copyright Copyright (c) 2003-2012 Aereus Corporation (http://www.aereus.com)
 */
require_once('lib/AntMail/Account.php');
require_once('lib/AntMail/Backend.php');
require_once('lib/CAntObject.php');
require_once('lib/Object/EmailMessage.php');
require_once('lib/AntObjectSync.php');
 
class AntMail_Sync
{
    /**
     * Instance of CDatabase
     *
     * @var Object
     */
     private $dbh = null;
     
     /**
     * Instance of CUser
     *
     * @var Object
     */
     private $user = null;
     
     /**
     * Temporary Folder
     *
     * @var String
     */
     private $tempFolder = null;
     
     /**
     * Determines how many email messages to sync
     * If null, it will sync all messages
     *
     * @var Integer
     */
     public $syncCount = null;
    
    /**
     * Class constructor
     * 
     * @param Object $dbh           Instance of CDatabase
     * @param Object $user          Instance of CUser
     * @param String $type          Determines which mail type to use (e.g. imap)
     *     
     */
    public function __construct($dbh, $user)
    {
        $this->dbh = $dbh;
        $this->user = $user;
        $this->tempFolder = (AntConfig::getInstance()->data_path) ? AntConfig::getInstance()->data_path."/tmp" : sys_get_temp_dir();
	}

	 /**
	 * Sync a mailbox
	 *
	 * @param int $mailboxId The id of the mailbox to sync
	 * @param AntMail_Account $account Optional account to sync, if not set then all accounts will sync
	 * @return int[] Array of message ids(Netric no mailstore) that were downloaded and saved
	 */
	public function syncMailbox($mailboxId, $account=null)
	{
		$accounts = array();

		if ($account)
			$accounts[] = $account;
		else
			$accounts = $this->user->getEmailAccounts();

        $ret = array();

		$mailObj = CAntObject::factory($this->dbh, "email_message", null, $this->user);
		$mailboxPath = $mailObj->getGroupingPath("mailbox_id", $mailboxId);

		foreach ($accounts as $accountObj)
		{
            // When syncing emails, account type should not be empty
            if(empty($accountObj->type))
                continue;

			$backend = $accountObj->getBackend();

			// Get object sync partnership and collection
			$syncPartner = $accountObj->getSyncPartner();
			$conditions = array(
                array(
                    "blogic"=>"and",
                    "field"=>"email_account",
                    "operator"=>"is_equal",
                    "condValue"=>$accountObj->id,
                ),
                array(
                    "blogic"=>"and",
                    "field"=>"mailbox_id",
                    "operator"=>"is_equal",
                    "condValue"=>$mailboxId,
                ),
            );
			$syncColl = $syncPartner->getEntityCollection("email_message", $conditions);
            // Create collection
            if (!$syncColl)
            {
                $serviceManager = ServiceLocatorLoader::getInstance($this->dbh)->getServiceManager();
                $syncColl = \Netric\EntitySync\Collection\CollectionFactory::create($serviceManager, \Netric\EntitySync\EntitySync::COLL_TYPE_ENTITY);
                $syncColl->setObjType("email_message");
                $syncColl->setConditions($conditions);
                $syncPartner->addCollection($syncColl);
                $serviceManager->get("EntitySync_DataMapper")->savePartner($syncPartner);
            }

			// First send changes to server
			// --------------------------------------------------------------------
			$stats = $syncColl->getExportChanged();
			foreach ($stats as $stat)
			{
				$obj = CAntObject::factory($this->dbh, "email_message", $stat['id'], $this->user);

				switch ($stat['action'])
				{
				case 'change':
					if ($obj->getValue("flag_seen") == 't' || $obj->getValue("flag_seen") === true)
						$backend->processUpsync($mailboxPath, $obj->getValue("message_uid"), "read", true);
					else
						$backend->processUpsync($mailboxPath, $obj->getValue("message_uid"), "read", false);

					if ($obj->getValue("flag_flagged") == 't')
						$backend->processUpsync($mailboxPath, $obj->getValue("message_uid"), "flagged", true);
					else
						$backend->processUpsync($mailboxPath, $obj->getValue("message_uid"), "flagged", false);

                    $syncColl->logExported($stat['id'], $obj->getValue("commit_id"));
					break;

				case 'delete':
					//$backend->debug = $this->debug;
					$backend->processUpsync($mailboxPath, $obj->getValue("message_uid"), "deleted", null);
                    $syncColl->logExported($stat['id'], null);
                    break;
				}
				//$syncColl->deleteStat($stat['id'], $mailboxId);
                $syncColl->setLastCommitId($obj->getValue("commit_id"));


				// Check for error
				$error = $backend->getLastError();
				if ($error)
					AntLog::getInstance()->error("Error trying to send change to mailserver for msg[{$stat['id']}]:" . $error);
			}
			
			// Now get new messages from the server and import
			// --------------------------------------------------------------------
			$emailList = $backend->getMessageList($mailboxPath);

			if (is_array($emailList))
			{
				$importList = array();
				foreach($emailList as $email)
					$importList[] = array("uid"=>$email['uid'], "revision"=>1);

				$stats = $syncColl->getImportChanged($importList);
			}
			else
			{
				$stats = array(); // Do nothing, could not connect to the server
			}

			foreach ($stats as $stat) // $msg = array('uid', 'object_id', 'action', 'revision')
			{
				switch ($stat['action'])
				{
				case 'change':
					// Set email meta data from server list
					$emailMeta = null;
					foreach ($emailList as $svrEmail)
					{
						if ($svrEmail['uid'] == $stat['uid']) 
						{
							$emailMeta = $svrEmail;
							break; // stop the loop
						}
					}

					if (isset($stat['local_id']))
					{
						$emailObj = CAntObject::factory($this->dbh, "email_message", $stat['local_id'], $this->user);
						$emailObj->setValue("flag_seen", $emailMeta['seen']==1?'t':'f');            
						$emailObj->setValue("flag_flagged", $emailMeta['flagged']==1?'t':'f');
						$mid = $emailObj->save();
					}
					else
					{
						$mid = $this->importEmail($emailMeta, $accountObj, $mailboxId);

					}

					if ($mid)
					{
                        $emailObj = CAntObject::factory($this->dbh, "email_message", $mid, $this->user);
						$syncColl->logImported($stat['uid'], $emailObj->revision, $mid);
						$ret[] = $mid;
					}

					break;

				case 'delete':
					if (isset($stat['local_id']) && $backend->isTwoWaySync())
					{
						$emailObj = CAntObject::factory($this->dbh, "email_message", $stat['local_id'], $this->user);
						if ($emailObj->getValue("f_deleted") != 't')
							$emailObj->remove();

						$ret[] = $stat['local_id'];
					}

					$syncColl->logImported($stat['uid'], $stat['revision'], null);

					break;
				}
			}
		}

		return $ret;
	}

	/**
	 * Synchronize all mailboxes
     *
     * NOTE: This is currently not in use anywhere but we have left it
     * uncommented because unit tests are still running against it. There has
     * been no request to sync the mailboxes of imap servers as of yet so
     * we can revisit this finish the TODO sections below.
	 *
	 * @param AntMail_Account $account Optional account to sync, if not set then all accounts will sync
	 * @return int[] Array of mailbox ids(Netric groupings) that were downloaded and saved
	 */
	public function syncMailboxes($accountObj=null)
	{
		// When syncing emails, account type should not be empty
		if(empty($accountObj->type))
			return array();
		
		$backend = $accountObj->getBackend();

		// Get object sync partnership and collection
		$syncPartner = $accountObj->getSyncPartner();

        $syncPartner = $accountObj->getSyncPartner();
        $conditions = array(
            array(
                "blogic"=>"and",
                "field"=>"email_account",
                "operator"=>"is_equal",
                "condValue"=>$accountObj->id,
            )
        );
        $syncColl = $syncPartner->getGroupingCollection("email_message", "mailbox_id", $conditions);
        // Create collection
        if (!$syncColl)
        {
            $serviceManager = ServiceLocatorLoader::getInstance($this->dbh)->getServiceManager();
            $syncColl = \Netric\EntitySync\Collection\CollectionFactory::create($serviceManager, \Netric\EntitySync\EntitySync::COLL_TYPE_GROUPING);
            $syncColl->setObjType("email_message");
            $syncColl->setFieldName("mailbox_id");
            $syncColl->setConditions($conditions);
            $syncPartner->addCollection($syncColl);
            $serviceManager->get("EntitySync_DataMapper")->savePartner($syncPartner);
        }

		// First send changes to server
		// --------------------------------------------------------------------
		$stats = $syncColl->getExportChanged();
		foreach ($stats as $stat)
		{
			/* TODO: for now we are not sending mailboxes
			$obj = CAntObject::factory($this->dbh, "email_message", $stat['id'], $this->user);
			switch ($stat['action'])
			{
			case 'change':
				if ($obj->getValue("flag_read") == 't')
					$backend->processUpsync($mailboxPath, $obj->getValue("message_uid"), "read", true);
				if ($obj->getValue("flag_flagged") == 't')
					$backend->processUpsync($mailboxPath, $obj->getValue("message_uid"), "flagged", true);
				break;

			case 'delete':
				$backend->debug = $this->debug;
				$backend->processUpsync($mailboxPath, $obj->getValue("message_uid"), "deleted", null);
				break;
			}
			*/
		}
		

		// Now get new messages from the server and import
		// --------------------------------------------------------------------
		$mailboxList = $backend->getMailboxes();

		if (is_array($mailboxList))
		{
			$stats = $syncColl->getImportChanged($mailboxList);
            foreach ($stats as $stat)
            {
                // TODO: create groupings and save
                //$syncPartner->logImported($uniqueId, $revision, $localId);
            }
            // TODO: after count($stats)==0 then $syncColl->fastForwardToHead();
		}
		else
		{
			$stats = array(); // Do nothing, could not connect to the server
		}

		// Update last full sync flag
		$accountObj->tsLastFullSync = time();
		$accountObj->save();

		return $stats;
	}

    /**
     * Syncrhonize server-side message to the local database store
     *
     * @param array $email Contains the email message data including msgno and uid
     * @param integer $account Email account used when sync-ing
     * @param integer $mailboxId Mailbox Id used when sync-ing
     */
    public function syncEmail($email, $account, $mailboxId)
    {
		$emailObj = CAntObject::factory($this->dbh, "email_message", null, $this->user);

		$mid = null;

		if ($email['uid'])
		{
			if($account->type == "pop3")
			{
				// if unique id is supported in pop3 then it is always unique so we can check all mailboxes
				$mid = $emailObj->getSyncEmailId($email['uid'], $account->id);

				if($mid > 0)
					return false; // No need to save email if it already exists in POP3 because there are no flags
			}
			else
			{
				// uid in imap is limited to the current mailbox
				$mid = $emailObj->getSyncEmailId($email['uid'], $account->id, $mailboxId);
			}
		}
        
		// Check if we are inserting new or updating
		if ($mid)
		{
            // Use the $mid to update the existing email
            $emailObj = CAntObject::factory($this->dbh, "email_message", $mid, $this->user);
            $emailObj->setValue("flag_seen", $email['seen']==1?'t':'f');            
			$emailObj->setValue("flag_flagged", $email['flagged']==1?'t':'f');
            $emailObj->save();
		}
		else
		{
			// Insert new message
			// TODO: maybe we should stream this rather than load the message into memory?
            $mimeEmail = $account->getBackend()->getFullMessage($email['msgno']);
        	$filePath = $this->saveTempFile($mimeEmail);

			// Import the message
			$newEmail = new CAntObject_EmailMessage($this->dbh, null, $this->user);
        	$newEmail->setValue("message_uid", $email['uid']);
			$newEmail->setValue("mailbox_id", $mailboxId);
			$newEmail->setValue("email_account", $account->id);
			$mid = $newEmail->import($filePath, null, true); // Last param saves a raw copy of the original
			unlink($filePath);
            
            return $mid;
		}
	}

	/**
     * Syncrhonize server-side message to the local database store
     *
     * @param array $email Contains the email message data including msgno and uid
     * @param integer $account Email account used when sync-ing
     * @param integer $mailboxId Mailbox Id used when sync-ing
     */
    public function importEmail($email, $account, $mailboxId)
    {
		$emailObj = CAntObject::factory($this->dbh, "email_message", null, $this->user);

		// First make sure the message is not already imported - no duplicates
		$list = new CAntObjectList($this->dbh, "email_message");
		$list->addCondition("and", "mailbox_id", "is_equal", $mailboxId);
		$list->addCondition("and", "message_uid", "is_equal", $email['uid']);
		$list->addCondition("and", "email_account", "is_equal", $account->id);
		$list->getObjects();
		if ($list->getNumObjects() > 0)
			return false;

		// Also checked previously deleted
		$list = new CAntObjectList($this->dbh, "email_message");
		$list->addCondition("and", "mailbox_id", "is_equal", $mailboxId);
		$list->addCondition("and", "message_uid", "is_equal", $email['uid']);
		$list->addCondition("and", "email_account", "is_equal", $account->id);
		$list->addCondition("and", "f_deleted", "is_equal", "t");
		$list->getObjects();
		if ($list->getNumObjects() > 0)
			return false;

		// Insert new message
		// TODO: maybe we should stream this rather than load the message into memory?
		$mimeEmail = $account->getBackend()->getFullMessage($email['msgno']);
		$filePath = $this->saveTempFile($mimeEmail);

		// Import the message
		$newEmail = new CAntObject_EmailMessage($this->dbh, null, $this->user);
		$newEmail->ignoreSupression = true;
		$newEmail->setValue("message_uid", $email['uid']);
		$newEmail->setValue("mailbox_id", $mailboxId);
		$newEmail->setValue("email_account", $account->id);
		$mid = $newEmail->import($filePath, null, true); // Last param saves a raw copy of the original
		unlink($filePath);
        
        /*
        // Check if account has a forward turned on
        if ($account->forward && $newEmail->getValue("flag_spam")!='t')
        {
            $newEmail->setHeader("To", $account->forward);
            // Send but do not save any changes
            $newEmail->send(false); 
            
            // TODO: keep a copy of local?
        }
         */
		
		return $mid;
	}

    /**
     * Saves the mime email into the temp file
     *
     * @param string $mimeEmail      Mime Email
     */
    public function saveTempFile($mimeEmail)
    {
        if (!file_exists($this->tempFolder))
            @mkdir($this->tempFolder, 0777, true);        
        $tmpFile = tempnam($this->tempFolder, "em");
        
        // Normalize new lines to \r\n
        if ($mimeEmail)
        {
            $handle = @fopen($tmpFile, "w+");
            fwrite($handle, preg_replace('/\r?\n$/', '', $mimeEmail)."\r\n"); // Write the email message content
            
            return $tmpFile;
        }
        else
            return null;
    }
}
