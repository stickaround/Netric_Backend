<?php
/**
 * ANT Backend for ActiveSync
 *
 * This function will be used as an interface between ANT backend and ActiveSync for smart phones
 *
 * @category  ASync
 * @package   BackendAnt
 * @copyright Copyright (c) 2003-2011 Aereus Corporation (http://www.aereus.com)
 */

// ANT includes
include_once(dirname(__FILE__)."/../../../lib/AntConfig.php");
include_once("lib/AntLog.php");
include_once("lib/Ant.php");
include_once("lib/AntUser.php");
include_once("lib/AntObjectSync.php");
include_once("lib/AntFs.php");
include_once("lib/Email.php");
include_once("lib/AntCalendar.php");
include_once("lib/ServiceLocatorLoader.php");

// processing of RFC822 messages
include_once('include/mimeDecode.php');
//require_once('include/z_RFC822.php');
require_once('Mail/RFC822.php');

// Local backend files
include_once('lib/default/diffbackend/diffbackend.php');
require_once("backend/ant/importer.php");
require_once("backend/ant/exporter.php");

/**
 * Define special root folders
 */
define("ASYNC_ROOT_CONTACTS", "contacts_root");
define("ASYNC_ROOT_CALENDAR", "calendar_root"); // we need to figure out how to add multiple calendars
define("ASYNC_ROOT_TASKS", "tasks_root");
define("ASYNC_ROOT_NOTES", "notes_root");

/**
 * ANT backend class
 */
//class BackendAnt implements IBackend
class BackendAnt extends BackendDiff
{
	/**
	 * AntObjectSync partnership
	 *
	 * @var AntObjectSync_Partner
	 */
	public $partnership = null;

	/**
	 * Local cache of sync collections
	 *
	 * @var AntObjectSync_Collection[]
	 */
	public $syncCollections = array();

	/**
     * Reference to current user object
     *
     * @var AntUser
	 */
	public $user;

	/**
     * Authenticated user name
     *
     * @var string
	 */
    public $username = null;

	/**
     * Unique id of currently connected device
     *
     * @var string
	 */
   	private $devid;

	/**
     * Reference to current ANT account object
     *
     * @var Ant
	 */
	public $ant = null;

	/**
	 * Handle to account database
	 *
	 * @var CDatabase $dbh
	 */
	public $dbh = null;

	/**
	 * The device id of the current request
	 *
	 * @var string
	 */
	public $deviceId = null;

	/**
	 * Test mode flag for unit tests
	 *
	 * @var bool
	 */
	public $testMode = false;

	/**
	 * Folders we are watching for changes
	 *
	 * @var array
	 */
    protected $sinkfolders;

	/**
     * Returns a IStateMachine implementation used to save states
     *
     * @access public
     * @return boolean/object       if false is returned, the default Statemachine is
     *                              used else the implementation of IStateMachine
	public function GetStateMachine()
	{
		// Eventually we will move this back to the DB, but for now just store
		// in the local file system if needed.
		return false;
	}
     */

    /**
     * Returns a ISearchProvider implementation used for searches
     *
     * @access public
     * @return object       Implementation of ISearchProvider
	public function GetSearchProvider()
	{
		return false; // we do not support search just yet
	}
     */

    /**
     * Indicates which AS version is supported by the backend.
     * Depending on this value the supported AS version announced to the
     * mobile device is set.
     *
     * @access public
     * @return string       AS version constant
     */
	public function GetSupportedASVersion()
	{
		return ZPush::ASV_14;
	}

    /**
     * Authenticates the user
     *
     * @param string        $username
     * @param string        $domain
     * @param string        $password
     *
     * @access public
     * @return boolean
     * @throws FatalException   e.g. some required libraries are unavailable
     */
	public function Logon($username, $domain, $password)
	{
		$this->username = $username;
		$this->ant = new Ant();
		$this->dbh = $this->ant->dbh;

		// Now check user table for user name and password combinations
		$userid = AntUser::authenticate($username, $password, $this->dbh);
		if ($userid)
		{
			$this->user = $this->ant->getUser($userid);

			if ($this->user->timezoneName)
				date_default_timezone_set($this->user->timezoneName);
		}

		// The deviceId is required
		if (!$this->deviceId)
			$this->deviceId = Request::GetDeviceID();

		if (!$this->deviceId)
			throw new AuthenticationRequiredException("No device ID is defined in this request and it is required");


		// Turn of lazy stats for the AntObjectSync functionality
		AntConfig::getInstance()->setValue("obj_sync_lazy_stat", null, false);

		if ($userid)
		{
			AntLog::getInstance()->info("Logon: $username, $domain, ******* [success]");
        	return true;
		}
		else
		{
			AntLog::getInstance()->info("Logon: $username, $domain, ******* [failed]");
        	return false;
		}
	}

    /**
     * Setup the backend to work on a specific store or checks ACLs there.
     * If only the $store is submitted, all Import/Export/Fetch/Etc operations should be
     * performed on this store (switch operations store).
     * If the ACL check is enabled, this operation should just indicate the ACL status on
     * the submitted store, without changing the store for operations.
     * For the ACL status, the currently logged on user MUST have access rights on
     *  - the entire store - admin access if no folderid is sent, or
     *  - on a specific folderid in the store (secretary/full access rights)
     *
     * The ACLcheck MUST fail if a folder of the authenticated user is checked!
     *
     * @param string        $store              target store, could contain a "domain\user" value
     * @param boolean       $checkACLonly       if set to true, Setup() should just check ACLs
     * @param string        $folderid           if set, only ACLs on this folderid are relevant
     *
     * @access public
     * @return boolean
     */
	public function Setup($store, $checkACLonly = false, $folderid = false)
	{
		if (!isset($this->user))
			return false;

		return true;
	}

    /**
     * Logs off
     * non critical operations closing the session should be done here
     *
     * @access public
     * @return boolean
     */
	public function Logoff()
	{
		return true;
	}

    /**
     * Returns an array of SyncFolder types with the entire folder hierarchy
     * on the server (the array itself is flat, but refers to parents via the 'parent' property
     *
     * provides AS 1.0 compatibility
     *
     * @access public
     * @return array SYNC_FOLDER
     */
	public function GetHierarchy()
	{
		$folders = array();

        $fl = $this->GetFolderList();

		foreach($fl as $f)
		{
            $folders[] = $this->getFolder($f['id']);
        }

		// Return array of SyncFolder(s)
        return $folders;
	}

    /**
     * Returns the importer to process changes from the mobile
     * If no $folderid is given, hierarchy data will be imported
     * With a $folderid a content data will be imported
     *
     * @param string        $folderid (opt)
     *
     * @access public
     * @return object       implements IImportChanges
     * @throws StatusException
     */
	public function GetImporter($folderid = false)
	{
		//return new ImportChangesAnt($this, $folderid);
		return new ImportChangesDiff($this, $folderid);;
	}

    /**
     * Returns the exporter to send changes to the mobile
     * If no $folderid is given, hierarchy data should be exported
     * With a $folderid a content data is expected
     *
     * @param string        $folderid (opt)
     *
     * @access public
     * @return object       implements IExportChanges
     * @throws StatusException
     */
	public function GetExporter($folderid = false)
	{
		return new ExportChangesDiff($this, $folderid);;
		//return new ExportChangesAnt($this, $folderid);
	}

    /**
     * Sends an e-mail
     * This messages needs to be saved into the 'sent items' folder
     *
     * Basically two things can be done
     *      1) Send the message to an SMTP server as-is
     *      2) Parse the message, and send it some other way
     *
     * @param SyncSendMail        $sm         SyncSendMail object
     *
     * @access public
     * @return boolean
     * @throws StatusException
     */
	public function SendMail($sm)
	{
		$email = CAntObject::factory($this->dbh, "email_message", null, $this->user);

		$mobj = new Mail_mimeDecode($sm->mime);
		/*
		$message = $mobj->decode(array('decode_headers' => true, 'decode_bodies' => true, 
			'include_bodies' => true, 'input' => $rfc822, 'crlf' => "\r\n", 'charset' => 'utf-8'));
		 */
		$message = $mobj->decode(array('decode_headers' => true, 'decode_bodies' => true, 'include_bodies' => true, 'charset' => 'utf-8'));

        $toaddr = $ccaddr = $bccaddr = "";
        if(isset($message->headers["to"]))
			$email->setHeader("To", $this->parseAddr(Mail_RFC822::parseAddressList($message->headers["to"])));
        if(isset($message->headers["cc"]))
			$email->setHeader("Cc", $this->parseAddr(Mail_RFC822::parseAddressList($message->headers["cc"])));
        if(isset($message->headers["bcc"]))
			$email->setHeader("Bcc", $this->parseAddr(Mail_RFC822::parseAddressList($message->headers["bcc"])));

        // save some headers when forwarding mails (content type & transfer-encoding)
        $headers = array();
        //$forward_h_ct = "";
        //$forward_h_cte = "";

        //$use_orgbody = false;

        // clean up the transmitted headers
        // remove default headers because we are using imap_mail
        $returnPathSet = false;
        //$body_base64 = false;
        $org_charset = "";
		foreach($message->headers as $k => $v) 
		{
			if ($k == "content-type") 
			{
                // save the original content-type header for the body part when forwarding
				/*
				if ($forward) 
				{
                    $forward_h_ct = $v;
                    continue;
                }
				 */

                // set charset always to utf-8
                //$org_charset = $v;

				if (!$forward)
                	$v = preg_replace("/charset=([A-Za-z0-9-\"']+)/", "charset=\"utf-8\"", $v);
            }

			/*
			if ($k == "content-transfer-encoding") 
			{
                // if the content was base64 encoded, encode the body again when sending
                if (trim($v) == "base64") $body_base64 = true;

                // save the original encoding header for the body part when forwarding
				if ($forward) 
				{
                    $forward_h_cte = $v;
                    continue;
                }
            }
			 */

            // if the message is a multipart message, then we should use the sent body
			/*
			if (!$forward && $k == "content-type" && preg_match("/multipart/i", $v))
			{
                $use_orgbody = true;
            }
			 */

            // check if "from"-header is set
			if ($k == "from")
			{
				$v = EmailGetUserName($this->dbh, $this->user->id, 'full_rep');
            }

            // check if "Reply-to"-header is set
			if ($k == "reply-to") 
			{
				$v = EmailGetUserName($this->dbh, $this->user->id, 'reply_to');
            }

            // check if "Return-Path"-header is set
			if ($k == "return-to") 
			{
				$v = EmailGetUserName($this->dbh, $this->user->id, 'reply_to');
            }

            if ($k)
				$email->setHeader(ucfirst($k), $v);
        }

		//if ($forward_h_ct)
			//$email->setHeader("Content-Type", $forward_h_ct);
		//if ($forward_h_ct)
			//$email->setHeader("Content-Transfer-Encoding", $forward_h_cte);

        // if this is a multipart message with a boundary, we must use the original body
		/*
		if ($use_orgbody) 
		{
            list(,$body) = $mobj->_splitBodyHeader($rfc822);
        }
        else
		{
            $body = $this->getBody($message);
		}
		 */

		//list(,$body) = $mobj->_splitBodyHeader($rfc822);

        // reply
		if (isset($reply) && isset($parent) && $reply && $parent) 
		{
			/*
            $this->imap_reopenFolder($parent);
            // receive entire mail (header + body) to decode body correctly
            $origmail = @imap_fetchheader($this->_mbox, $reply, FT_PREFETCHTEXT | FT_UID) . @imap_body($this->_mbox, $reply, FT_PEEK | FT_UID);
            $mobj2 = new Mail_mimeDecode($origmail);
            // receive only body
            $body .= $this->getBody($mobj2->decode(array('decode_headers' => false, 'decode_bodies' => true, 'include_bodies' => true, 'input' => $origmail, 'crlf' => "\n", 'charset' => 'utf-8')));
            // unset mimedecoder & origmail - free memory
            unset($mobj2);
            unset($origmail);
			 */
        }

        // encode the body to base64 if it was sent originally in base64 by the pda
        // the encoded body is included in the forward
        //if ($body_base64) $body = base64_encode($body);

        // forward
		if (isset($forward) && isset($parent) && $forward && $parent) 
		{
			/*
            $this->imap_reopenFolder($parent);
            // receive entire mail (header + body)
            $origmail = @imap_fetchheader($this->_mbox, $forward, FT_PREFETCHTEXT | FT_UID) . @imap_body($this->_mbox, $forward, FT_PEEK | FT_UID);

            // build a new mime message, forward entire old mail as file
            list($aheader, $body) = $this->mail_attach("forwarded_message.eml",strlen($origmail),$origmail, $body, $forward_h_ct, $forward_h_cte);

            // unset origmail - free memory
            unset($origmail);

            // add boundary headers
            $headers .= "\n" . $aheader;
			 */
        }

		try
		{
			$body = $this->getBody($message, "html");
			$email->setBody($body, "html");	
			if (!$this->testMode)
				$email->send();
		}
		catch (Exception $e)
		{
			throw new StatusException("ZarafaBackend::SendMail(): Error sending the message", SYNC_COMMONSTATUS_MAILSUBMISSIONFAILED);
		}

		unset($email);

        return true;
	}	

    /**
     * Returns all available data of a single message
     *
     * @param string            $folderid
     * @param string            $id
     * @param ContentParameters $contentparameters flag
     *
     * @access public
     * @return object(SyncObject)
     * @throws StatusException
     */
	public function Fetch($folderid, $id, $contentparameters)
	{
		switch ($folderid)
		{
		case "contacts_root":
			$contact = $this->getContact($id);
			if ($contact)
				return $contact;
			break;
		case "calendar_root":
			$app = $this->getAppointment($id);
			if ($app)
				return $app;
			break;
		case "tasks_root":
			$app = $this->getTask($id);
			if ($app)
				return $app;
			break;
		case "notes_root":
			break;
		default:
			$message = $this->getEmail($id, $contentparameters);
			if ($message)
				return $message;
			break;
		}
	}

    /**
     * Returns the waste basket
     *
     * The waste basked is used when deleting items; if this function returns a valid folder ID,
     * then all deletes are handled as moves and are sent to the backend as a move.
     * If it returns FALSE, then deletes are handled as real deletes
     *
     * @access public
     * @return string
     */
	public function GetWasteBasket()
	{
		// In netric we put things in the wastebasket by marking them as f_deleted
		return "Trash";
	}

    /**
     * Returns the content of the named attachment as stream. The passed attachment identifier is
     * the exact string that is returned in the 'AttName' property of an SyncAttachment.
     * Any information necessary to locate the attachment must be encoded in that 'attname' property.
     * Data is written directly - 'print $data;'
	 *
	 * All email attachments in ANT are stored in the AntFs file system with a unique id. That unique
	 * id is what is sent as the attname when the message is initially pulled.
     *
     * @param string        $attname
     * @return SyncItemOperationsAttachment
     * @throws StatusException
     */
	public function GetAttachmentData($attname)
	{
		if (!is_numeric($attname))
		{
			throw new StatusException(sprintf("AntBackend::GetAttachmentData('%s'): Error, attachment requested for non-existing item", $attname), 
										SYNC_ITEMOPERATIONSSTATUS_INVALIDATT);
		}

		$obj = new CAntObject($this->dbh, "email_message_attachment", $attname, $this->user);
		$attachment = new SyncItemOperationsAttachment();

		if ($obj->getValue("file_id"))
		{
			$antfs = new AntFs($this->dbh, $this->user);
			$file = $antfs->openFileById($obj->getValue("file_id"));
			if ($file)
			{
				$attachment->data = AntFsStreamWrapper::OpenFile($file);
				$attachment->contenttype = $file->getContentType();
				//$file->stream();
			}
			
			return true;
		}
		else
		{
			throw new StatusException(sprintf("AntBackend::GetAttachmentData('%s'): No file_id was set for the requested attachment", $attname), 
										SYNC_ITEMOPERATIONSSTATUS_INVALIDATT);
		}

		return $attachment;
	}

    /**
     * Deletes all contents of the specified folder.
     * This is generally used to empty the trash (wastebasked), but could also be used on any
     * other folder.
     *
     * @param string        $folderid
     * @param boolean       $includeSubfolders      (opt) also delete sub folders, default true
     *
     * @access public
     * @return boolean
     * @throws StatusException
     */
	public function EmptyFolder($folderid, $includeSubfolders = true)
	{
		return false; // TODO: we need to implement this
	}

    /**
     * Processes a response to a meeting request.
     * CalendarID is a reference and has to be set if a new calendar item is created
     *
     * @param string        $requestid      id of the object containing the request
     * @param string        $folderid       id of the parent folder of $requestid
     * @param string        $response
     *
     * @access public
     * @return string       id of the created/updated calendar obj
     * @throws StatusException
     */
	public function MeetingResponse($requestid, $folderid, $response)
	{
		return false; // TODO: we need to implement this
	}

    /**
     * Indicates if the backend has a ChangesSink.
     * A sink is an active notification mechanism which does not need polling.
     *
     * @access public
     * @return boolean
     */
	public function HasChangesSink()
	{
        $this->sinkfolders = array();
		return true;
	}

    /**
     * The folder should be considered by the sink.
     * Folders which were not initialized should not result in a notification
     * of IBacken->ChangesSink().
     *
     * @param string        $folderid
     *
     * @access public
     * @return boolean      false if there is any problem with that folder
     */
	public function ChangesSinkInitialize($folderid)
	{
		$this->sinkfolders[] = array(
			"id"=>$folderid,
			"grouping"=>$this->getGroupingId($folderid),
		);
		return true;
	}

    /**
     * The actual ChangesSink.
     * For max. the $timeout value this method should block and if no changes
     * are available return an empty array.
     * If changes are available a list of folderids is expected.
     *
     * @param int           $timeout        max. amount of seconds to block
     *
     * @access public
     * @return array
     */
	public function ChangesSink($timeout = 30)
	{
		$notifications = array();
        $stopat = time() + $timeout - 1;

		while($stopat > time() && count($notifications)==0) 
		{
			foreach ($this->sinkfolders as $folder) 
			{
				$collection = $this->getSyncCollection($folder['id']);

				// Get the number of changes since last sync
				if ($collection->isBehindHead())
				{
					// Sky Stebnicki: For now we just reset all stats because we are pretty much
					// only using this as a flag to track whether a collection has any changes.
					//$collection->resetStats($folder['grouping']);
					$collection->fastForwardToHead(); // Do not filter the grouping
					$notifications[] = $folder['id'];

                    // Save the collection
                    $serviceManager = ServiceLocatorLoader::getInstance($this->dbh)->getServiceManager();
                    $serviceManager->get("EntitySync_DataMapper")->savePartner($this->partnership);
					
					/*
					$changes = $collection->getChangedObjects($folder['grouping']); 
					// Note: we might want to set autoclear (second param) above to false once we change to custom exporter
					// because stats will be used to get changed objects rather than the local file stats

					if (count($changes))
						$notifications[] = $folder['id'];
					 */
				}
            }

            if (count($notifications)==0)
                sleep(5);
		}

		return $notifications;
	}

    /**
     * Applies settings to and gets informations from the device
     *
     * @param SyncObject    $settings (SyncOOF or SyncUserInformation possible)
     *
     * @access public
     * @return SyncObject   $settings
     */
	public function Settings($settings)
	{
		if ($settings instanceof SyncOOF || $settings instanceof SyncUserInformation)
            $settings->Status = SYNC_SETTINGSSTATUS_SUCCESS;

		// TODO: SyncOOF wants to know th out of office status

		// TODO: User information would like to know the users email address

        return $settings;
	}


	// BackendDiff functions. These are not needed specially for the diff backend.
	// ====================================================================================
	
	/**
	 * This is just an interface to $this->Fetch
	 *
	 * I'm not sure why the diff exporter uses this rather than Fetch but it does
	 *
     * @param string            $folderid
     * @param string            $id
     * @param ContentParameters $contentparameters flag
     * @return object(SyncObject)
     * @throws StatusException
	 */
	public function GetMessage($folderid, $id, $contentparameters) 
	{
		return $this->Fetch($folderid, $id, $contentparameters);
	}

	/**
     * Called when a message has been changed on the mobile. The new message must be saved to disk.
     * The return value must be whatever would be returned from StatMessage() after the message has been saved.
     * This way, the 'flags' and the 'mod' properties of the StatMessage() item may change via ChangeMessage().
     * This method will never be called on E-mail items as it's not 'possible' to change e-mail items. It's only
     * possible to set them as 'read' or 'unread'.
     *
     * @param string $folderid id of the folder
     * @param string $id id of the message
     * @param SyncXXX $message the SyncObject containing a message
	 * @param ContentParameters $contentparameters
     *
     * @access public
     * @return array                        same return value as StatMessage()
     * @throws StatusException              could throw specific SYNC_STATUS_* exceptions
     */
	public function ChangeMessage($folderid, $id, $message, $contentParameters) 
	{
		debugLog("ChangeMessage $folderid, $id");

		switch ($folderid)
		{
		case "contacts_root":
			$ret = $this->saveContact($id, $message);
			if ($ret)
			{
				$stat = $this->StatMessage($folderid, $ret->id);
				return $stat;
			}
			break;
		case "calendar_root":
			$ret = $this->saveAppointment($id, $message);

			if ($ret)
			{
				$stat = $this->StatMessage($folderid, $ret->id); 
				//debugLog("\tChangeMessage ".var_export($stat, true));
				return $stat;
			}
			break;
		case "tasks_root":
			$ret = $this->saveTask($id, $message);

			if ($ret)
				return $this->StatMessage($folderid, $ret->id);
			break;
		default:
			$ret = $this->saveEmailMessage($id, $message);

			if ($ret)
				return $this->StatMessage($folderid, $ret->id);
			break;
		}

        return false;
    }

	/**
     * Changes the 'read' flag of a message on disk. The $flags
     * parameter can only be '1' (read) or '0' (unread). After a call to
     * SetReadFlag(), GetMessageList() should return the message with the
     * new 'flags' but should not modify the 'mod' parameter. If you do
     * change 'mod', simply setting the message to 'read' on the mobile will trigger
     * a full resync of the item from the server.
     *
     * @param string        $folderid       id of the folder
     * @param string        $id             id of the message
     * @param int           $flags          read flag of the message
     * @param ContentParameters $contentparameters flag
     *
     * @access public
     * @return boolean                      status of the operation
     * @throws StatusException              could throw specific SYNC_STATUS_* exceptions
     */
	public function SetReadFlag($folderid, $id, $flags, $contentParameters) 
	{
		debugLog("SetReadFlag $folderid, $id, $flags");

		switch ($folderid)
		{
		case "contacts_root":
		case "calendar_root":
		case "tasks_root":
			return false;
			break;
		default:
			$this->markEmailMessageRead($id, ($flags) ? true : false);
			break;
		}
		
        return true;
    }

	/**
     * Called when the user has requested to delete (really delete) a message. Usually
     * this means just unlinking the file its in or somesuch. After this call has succeeded, a call to
     * GetMessageList() should no longer list the message. If it does, the message will be re-sent to the mobile
     * as it will be seen as a 'new' item. This means that if this method is not implemented, it's possible to
     * delete messages on the PDA, but as soon as a sync is done, the item will be resynched to the mobile
     *
     * @param string        $folderid       id of the folder
     * @param string        $id             id of the message
     * @param ContentParameters $contentparameters flag
     *
     * @access public
     * @return boolean                      status of the operation
     * @throws StatusException              could throw specific SYNC_STATUS_* exceptions
     */
	public function DeleteMessage($folderid, $id, $contentParameters) 
	{
		debugLog("DeleteMessage $folderid, $id");

		$objType = "";
		switch ($folderid)
		{
		case "contacts_root":
			$this->deleteContact($id);
			break;
		case "calendar_root":
			$this->deleteAppointment($id);
			break;
		case "tasks_root":
			$this->deleteTask($id);
			break;
		default:
			$this->deleteEmailMessage($id);
			break;
		}

        return true;
    }

	/**
     * Called when the user moves an item on the PDA from one folder to another. Whatever is needed
     * to move the message on disk has to be done here. After this call, StatMessage() and GetMessageList()
     * should show the items to have a new parent. This means that it will disappear from GetMessageList()
     * of the sourcefolder and the destination folder will show the new message
     *
     * @param string        $folderid       id of the source folder
     * @param string        $id             id of the message
     * @param string        $newfolderid    id of the destination folder
     * @param ContentParameters $contentparameters flag
     *
     * @access public
     * @return boolean                      status of the operation
     * @throws StatusException              could throw specific SYNC_MOVEITEMSSTATUS_* exceptions
     */
	public function MoveMessage($folderid, $id, $newfolderid, $contentParameters)
	{
		if (strtolower($newfolderid) == "trash")
			return $this->deleteEmailMessage($id);
		else
			return $this->moveEmailMessage($id, $newfolderid);
	}


	/**
	 * Get a full message list for the specific folder ID
	 *
	 * @param string        $folderid       id of the parent folder
     * @param long          $cutoffdate     timestamp in the past from which on messages should be returned
	 * @return array
	 */
	public function GetMessageList($folderid, $cutoffDate) 
	{
        $messages = array();

		switch ($folderid)
		{
		case "contacts_root":
			$objList = new CAntObjectList($this->dbh, "contact_personal", $this->user);
			$objList->addCondition("and", "user_id", "is_equal", $this->user->id);
			$objList->addOrderBy("date_changed", "desc");
			$objList->getObjects(0, 2000);
			$num = $objList->getNumObjects();
			for ($i = 0; $i < $num; $i++)
			{
				$objMin = $objList->getObjectMin($i);	

				$message = array();
				$message["id"] = $objMin['id'];
				$message["mod"] = $objMin['revision'];
				$message["flags"] = 1; // always 'read'
				$messages[] = $message;
			}
			break;

		case "calendar_root":
			$cal = $this->user->getDefaultCalendar();
			$recur_processed = array();

			$objList = new CAntObjectList($this->dbh, "calendar_event", $this->user);
			$objList->addCondition("and", "calendar", "is_equal", $cal->id);
			if ($cutoffDate)
				$objList->addCondition("and", "ts_start", "is_greater_or_equal", date("Y-m-d", $cutoffDate));
			$objList->addOrderBy("ts_updated", "desc"); // Get last 1000 events
			$objList->addMinField("recurrence_pattern");
			$objList->getObjects(0, 1000);
			$num = $objList->getNumObjects();
			for ($i = 0; $i < $num; $i++)
			{
				$objMin = $objList->getObjectMin($i);	

				if ($objMin['recurrence_pattern'])
				{
					/*
					$tr_query = "select id from calendar_events_recurring_ex where recurring_id='".$row['recur_id']."' and event_id='".$row['id']."'";
					if (in_array($row['recur_id'], $recur_processed) && !$this->dbh->GetNumberRows($this->dbh->Query($tr_query)))
						continue; // skip
					 */

					// TODO: check for exception
					if (in_array($row['recurrence_pattern'], $recur_processed))
						continue; // skip

					$recur_processed[] = $row['recurrence_pattern'];
				}

				$message = array();
				$message["id"] = $objMin['id'];
				$message["mod"] = $objMin['revision'];
				$message["flags"] = 1; // always 'read'
				$messages[] = $message;
			}
			break;

		case "tasks_root":
            $objList = new CAntObjectList($this->dbh, "task", $this->user);
            $objList->addCondition("and", "user_id", "is_equal", $this->user->id);
            $objList->addOrderBy("date_entered", "desc");
            $objList->getObjects(0, 2000);
            $num = $objList->getNumObjects();
            for ($i = 0; $i < $num; $i++)
            {
                $objMin = $objList->getObjectMin($i);    

                $message = array();
                $message["id"] = $objMin['id'];
                $message["mod"] = $objMin['revision'];
                $message["flags"] = 1; // always 'read'
                $messages[] = $message;
            }
			break;

		default:
            $emailUserId = $this->user->getEmailUserId();
            
            // check if email user id is empty
            if(empty($emailUserId))
                $emailUserId = $this->user->getEmailUserId();
                
			$boxid = EmailGetMailboxPathId($this->dbh, $this->user->id, $folderid);
			$objList = new CAntObjectList($this->dbh, "email_message", $this->user);
			$objList->addMinField("flag_seen");
			$objList->addCondition("and", "mailbox_id", "is_equal", $boxid);
			if ($cutoffDate)
				$objList->addCondition("and", "message_date", "is_greater_or_equal", date("Y-m-d", $cutoffDate));
			$objList->addOrderBy("message_date", "desc");
			$objList->getObjects(0, 250);
			$num = $objList->getNumObjects();
			$total = $objList->getTotalNumObjects();
			for ($i = 0, $j = 0; $i < $num; $i++, $j++)
			{
				$objMin = $objList->getObjectMin($i);	


				$message = array();
				$message["id"] = $objMin['id'];
				$message["mod"] = ($objMin['revision']) ? $objMin['revision'] : 1;
				$message["flags"] = ($objMin['flag_seen'] == 't') ? 1 : 0;
				$messages[] = $message;
			}
			break;
		}

		return $messages;
    }

	/**
	 * Get the grouping/mailbox id from a folder
	 *
	 * @param string $folderId
	 */
	private function getGroupingId($folderId)
	{
		switch ($folderid)
		{
		case "contacts_root":
		case "calendar_root":
		case "tasks_root":
			return null;
			break;
		default:
			$msgObj = CAntObject::factory($this->dbh, "email_message", null, $this->user);
			return $msgObj->getGroupId($folderId);
			break;
		}
	}

	/**
	 * Get basic info for an item
	 *
	 * @param string $folderid The folder containing the item
	 * @param string $id The unique id of the item
	 * @return array("id", "mod"(revision), "flags")
	 */
	public function StatMessage($folderid, $id) 
	{
		debugLog("StatMessage $folderid, $id");

        $message = array();

        switch ($folderid)
		{
		case "contacts_root":
			$objList = new CAntObjectList($this->dbh, "contact_personal", $this->user);
			$objList->addCondition("and", "id", "is_equal", $id);
			$objList->getObjects(0, 1);
			$num = $objList->getNumObjects();
			if ($num)
			{
				$objMin = $objList->getObjectMin(0);	
				$message["id"] = $id;
				$message["mod"] = ($objMin['revision']) ? $objMin['revision'] : 1;
				$message["flags"] = 1;
			}

			/*
			$obj = new CAntObject($this->dbh, "contact_personal", $id, $this->user);
			if ($obj->id)
			{
				$message["id"] = $id;
				$message["mod"] = $obj->getValue("revision");
				$message["flags"] = 1;
			}
			*/

			break;
		case "calendar_root":
			$objList = new CAntObjectList($this->dbh, "calendar_event", $this->user);
			$objList->addCondition("and", "id", "is_equal", $id);
			$objList->getObjects(0, 1);
			$num = $objList->getNumObjects();
			if ($num)
			{
				$objMin = $objList->getObjectMin(0);	
				$message["id"] = $id;
				$message["mod"] = ($objMin['revision']) ? $objMin['revision'] : 1;
				$message["flags"] = 1;
			}

			/*
			$obj = new CAntObject($this->dbh, "calendar_event", $id, $this->user);
			if ($obj->id)
			{
				$message["id"] = $id;
				$message["mod"] = ($obj->getValue("revision")) ? $obj->getValue("revision") : 1;
				$message["flags"] = 1;
			}
			*/
			break;
		case "tasks_root":
			$objList = new CAntObjectList($this->dbh, "task", $this->user);
			$objList->addCondition("and", "id", "is_equal", $id);
			$objList->getObjects(0, 1);
			$num = $objList->getNumObjects();
			if ($num)
			{
				$objMin = $objList->getObjectMin(0);	
				$message["id"] = $id;
				$message["mod"] = ($objMin['revision']) ? $objMin['revision'] : 1;
				$message["flags"] = 1;
			}
			/*
			$result = $this->dbh->Query("select id, ts_updated, EXTRACT(EPOCH from ts_updated) as ts_changed
											from project_tasks where id='$id'");
			if ($this->dbh->GetNumberRows($result))
			{
				$row = $this->dbh->GetRow($result, 0);

				$time = ($row['ts_updated']) ? $row['ts_changed'] : 1;
				$message["mod"] = $time;
				$message["id"] = $id;
				$message["flags"] = 1;
			}
			*/
			break;
		default:            
			$objList = new CAntObjectList($this->dbh, "email_message", $this->user);
			$objList->addCondition("and", "id", "is_equal", $id);
			$objList->getObjects(0, 1);
			$num = $objList->getNumObjects();
			if ($num)
			{
				$obj = $objList->getObject(0);	
				$message["id"] = $obj->id;
				$message["mod"] = ($obj->getValue("revision")) ? $obj->getValue("revision") : 1;
				$message["flags"] = ($obj->getValue("flag_seen") == 't') ? 1 : 0;
			}
			/*
			// Can reference by id because in ANT every message has an absolute unique ID
			$obj = new CAntObject($this->dbh, "email_message", $id, $this->user);
			if ($obj->id)
			{
				$message["id"] = $id;
				$message["mod"] = ($obj->getValue("revision")) ? $obj->getValue("revision") : 1;
				$message["flags"] = ($obj->getValue("flag_seen") == 't') ? 1 : 0;
			}
			*/
			break;
		}

		return $message;
    }

	/**
	 * Return folder stats. 
	 *
	 * This means you must return an associative array with the following properties:
     * "id" => The server ID that will be used to identify the folder. It must be unique, and not too long
     *         How long exactly is not known, but try keeping it under 20 chars or so. It must be a string.
     * "parent" => The server ID of the parent of the folder. Same restrictions as 'id' apply.
     * "mod" => This is the modification signature. It is any arbitrary string which is constant as long as
     *          the folder has not changed. In practice this means that 'mod' can be equal to the folder name
     *          as this is the only thing that ever changes in folders. (the type is normally constant)
	 *
	 * @param string $id The unique ID of the folder to stat
	 * @return array('id', 'parent', 'mod') of the folder stats
     */
	public function StatFolder($id) 
	{
        $folder = $this->GetFolder($id);

        $stat = array();
        $stat["id"] = $id;
        $stat["parent"] = $folder->parentid;
        $stat["mod"] = $folder->displayname;

        return $stat;
    }

	/**
	 * Creates or modifies a folder
	 *
     * @param string $folderid id of the parent folder
     * @param string $oldid if empty -> new folder created, else folder is to be renamed
     * @param string $displayname new folder name (to be created, or to be renamed to)
     * @param int $type SYNC_FOLDER_TYPE_*
     */
	public function ChangeFolder($folderid, $oldid, $displayname, $type)
	{
		return false;
	}

	/**
	 * Delete folder
	 *
     * @param string $folderid id of the parent folder
     * @param string $oldid if empty -> new folder created, else folder is to be renamed
     * @param string $displayname new folder name (to be created, or to be renamed to)
     * @param int $type SYNC_FOLDER_TYPE_*
     */
	public function DeleteFolder($folderid, $parentid)
	{
		return false;
	}


	// ANT object getters - not part of the protocol interface
	// ====================================================================================

	/**
	 * Get a contact from ANT and convert to SyncObjet
	 *
	 * @param string $id The unique id of the contact to get
	 * @return SyncContact
	 */
	public function getContact($id)
	{
		$obj = new CAntObject($this->dbh, "contact_personal", $id, $this->user);
		$contact = new SyncContact();
		$contact->body = $obj->getValue('notes');
		$contact->bodysize = strlen($obj->getValue('notes'));
		$contact->bodytruncated = 0;
		$contact->businessphonenumber = $obj->getValue('phone_work');
		$contact->businesscity = $obj->getValue('business_city');
		//$contact->businesscountry = $row['businesscountry'];
		$contact->businesspostalcode = $obj->getValue('business_zip');
		$contact->businessstate = $obj->getValue('business_state');
		$contact->businessstreet = $obj->getValue('business_street');
		//$contact->categories = $row['categories'];
		$contact->companyname = $obj->getValue('company');
		$contact->email1address = $obj->getValue('email');
		$contact->email2address = $obj->getValue('email2');
		$contact->email3address = $obj->getValue('email_spouse');
		$contact->firstname = $obj->getValue('first_name');
		$contact->homecity = $obj->getValue('city');
		$contact->homepostalcode = $obj->getValue('zip');
		$contact->homestate = $obj->getValue('state');
		$contact->homestreet = $obj->getValue('street');
		$contact->homefaxnumber = $obj->getValue('fax');
		$contact->homephonenumber = $obj->getValue('phone_home');
		$contact->jobtitle = $obj->getValue('job_title');
		$contact->lastname = $obj->getValue('last_name');
		$contact->middlename = $obj->getValue('middle_name');
		$contact->pagernumber = $obj->getValue('pager');
		$contact->spouse = $obj->getValue('spouse_name');
		$contact->mobilephonenumber = $obj->getValue('phone_cell');
		$contact->nickname = $obj->getValue('nick_name');
		return $contact;
	}

	/**
	 * Get a calendar event from ANT and convert to SyncObjet
	 *
	 * @param string $id The unique id of the event to get
	 * @return SyncAppointment
	 */
	public function getAppointment($id)
	{
		debugLog("getAppointment $id");

		$cur_tz = date_default_timezone_get();
		$pulltz = ($this->user->timezoneName) ? $this->user->timezoneName : 'utc';

		$antEvent = new CAntObject($this->dbh, "calendar_event", $id, $this->user);

		if (!$antEvent->getValue("ts_end") || !$antEvent->getValue("ts_start"))
			return false;

		/*
		if ($appointment->starttime)
			$antEvent->setValue("ts_start", date("Y-m-d g:i A", $appointment->starttime));
		if ($appointment->endtime)
			$antEvent->setValue("ts_end", date("Y-m-d g:i A", $appointment->endtime));
		*/

		$appt = new SyncAppointment();
		$appt->subject = $antEvent->getValue("name");
		$appt->location = $antEvent->getValue("location");
		$appt->body = $antEvent->getValue("notes");
		$appt->alldayevent = ($antEvent->getValue("all_day") == 't') ? true : false;
		$appt->starttime = strtotime($antEvent->getValue("ts_start"));
		$appt->endtime = strtotime($antEvent->getValue("ts_end"));
		$appt->dtstamp = strtotime($antEvent->getValue("ts_changed"));
		$appt->busystatus 	= 2;
		$appt->meetingstatus= 0;

		// Create timezone
		$tz = TimezoneUtil::GetFullTZ();
		if (!$tz)
			$tz =  $this->getGMTTZ();
		$appt->timezone 	= base64_encode($this->getSyncBlobFromTZ($tz));

		$appt->recurrence 	= null;
		$appt->reminder		= 0;
		$appt->attendees	= null;
		$appt->uid			= $id;
		$appt->exceptions	= null;
		$appt->categories	= null;
		$appt->sensitivity	= 0;

		if ($antEvent->isRecurring())
		{
			$appt->recurrence = new SyncRecurrence();
			$rp = $antEvent->getRecurrencePattern();

			$appt->recurrance->interval = $rp->interval;

			switch ($rp->type)
			{
			case RECUR_DAILY:
				$appt->recurrence->type = 0;
				break;
			case RECUR_WEEKLY:
				$appt->recurrence->type = 1;
				if ($rp->dayOfWeekMask & WEEKDAY_SUNDAY)
					$appt->recurrence->dayofweek = $appt->recurrence->dayofweek | WEEKDAY_SUNDAY;
				if ($rp->dayOfWeekMask & WEEKDAY_MONDAY)
					$appt->recurrence->dayofweek = $appt->recurrence->dayofweek | WEEKDAY_MONDAY;
				if ($rp->dayOfWeekMask & WEEKDAY_TUESDAY)
					$appt->recurrence->dayofweek = $appt->recurrence->dayofweek | WEEKDAY_TUESDAY;
				if ($rp->dayOfWeekMask & WEEKDAY_WEDNESDAY)
					$appt->recurrence->dayofweek = $appt->recurrence->dayofweek | WEEKDAY_WEDNESDAY;
				if ($rp->dayOfWeekMask & WEEKDAY_THURSDAY)
					$appt->recurrence->dayofweek = $appt->recurrence->dayofweek | WEEKDAY_THURSDAY;
				if ($rp->dayOfWeekMask & WEEKDAY_FRIDAY)
					$appt->recurrence->dayofweek = $appt->recurrence->dayofweek | WEEKDAY_FRIDAY;
				if ($rp->dayOfWeekMask & WEEKDAY_SATURDAY)
					$appt->recurrence->dayofweek = $appt->recurrence->dayofweek | WEEKDAY_SATURDAY;
				break;
			case RECUR_MONTHLY:
				$appt->recurrence->type = 2;
				$appt->recurrence->dayofmonth = $rp->dayOfMonth;
				break;
			case RECUR_MONTHNTH:
				$appt->recurrence->type = 3;
				$appt->recurrence->weekofmonth = $rp->instance;
				if ($rp->dayOfWeekMask & WEEKDAY_SUNDAY)
					$appt->recurrence->dayofweek = $appt->recurrence->dayofweek | WEEKDAY_SUNDAY;
				if ($rp->dayOfWeekMask & WEEKDAY_MONDAY)
					$appt->recurrence->dayofweek = $appt->recurrence->dayofweek | WEEKDAY_MONDAY;
				if ($rp->dayOfWeekMask & WEEKDAY_TUESDAY)
					$appt->recurrence->dayofweek = $appt->recurrence->dayofweek | WEEKDAY_TUESDAY;
				if ($rp->dayOfWeekMask & WEEKDAY_WEDNESDAY)
					$appt->recurrence->dayofweek = $appt->recurrence->dayofweek | WEEKDAY_WEDNESDAY;
				if ($rp->dayOfWeekMask & WEEKDAY_THURSDAY)
					$appt->recurrence->dayofweek = $appt->recurrence->dayofweek | WEEKDAY_THURSDAY;
				if ($rp->dayOfWeekMask & WEEKDAY_FRIDAY)
					$appt->recurrence->dayofweek = $appt->recurrence->dayofweek | WEEKDAY_FRIDAY;
				if ($rp->dayOfWeekMask & WEEKDAY_SATURDAY)
					$appt->recurrence->dayofweek = $appt->recurrence->dayofweek | WEEKDAY_SATURDAY;
				break;
			case RECUR_YEARLY:
				$appt->recurrence->type = 5;
				$appt->recurrence->dayofmonth = $rp->dayOfMonth;
				break;
			case RECUR_YEARNTH:
				$appt->recurrence->type = 6;
				$appt->recurrence->weekofmonth = $rp->instance;
				if ($rp->dayOfWeekMask & WEEKDAY_SUNDAY)
					$appt->recurrence->dayofweek = $appt->recurrence->dayofweek | WEEKDAY_SUNDAY;
				if ($rp->dayOfWeekMask & WEEKDAY_MONDAY)
					$appt->recurrence->dayofweek = $appt->recurrence->dayofweek | WEEKDAY_MONDAY;
				if ($rp->dayOfWeekMask & WEEKDAY_TUESDAY)
					$appt->recurrence->dayofweek = $appt->recurrence->dayofweek | WEEKDAY_TUESDAY;
				if ($rp->dayOfWeekMask & WEEKDAY_WEDNESDAY)
					$appt->recurrence->dayofweek = $appt->recurrence->dayofweek | WEEKDAY_WEDNESDAY;
				if ($rp->dayOfWeekMask & WEEKDAY_THURSDAY)
					$appt->recurrence->dayofweek = $appt->recurrence->dayofweek | WEEKDAY_THURSDAY;
				if ($rp->dayOfWeekMask & WEEKDAY_FRIDAY)
					$appt->recurrence->dayofweek = $appt->recurrence->dayofweek | WEEKDAY_FRIDAY;
				if ($rp->dayOfWeekMask & WEEKDAY_SATURDAY)
					$appt->recurrence->dayofweek = $appt->recurrence->dayofweek | WEEKDAY_SATURDAY;
				break;
			}
		}

		// Termination
		if ($rp->dateEnd) 
		{
			$appt->recurrence->until = strtotime($rp->dateEnd);
		}

		return $appt;
	}

	/**
	 * Get a task from ANT and convert to SyncObjet
	 *
	 * @param string $id The unique id of the task to get
	 * @return SyncTask
	 */
	public function getTask($id)
	{
		debugLog("getContact $id");

		$obj = new CAntObject($this->dbh, "task", $id, $this->user);
        $task = new SyncTask();
        $task->subject = $obj->getValue('name');
        $task->body = $obj->getValue('notes');
        $task->complete = ($obj->getValue('done') == 't') ? 1 : 0;
		if ($obj->getValue('date_completed'))
        	$task->datecompleted = strtotime($obj->getValue('date_completed'));
		if ($obj->getValue('deadline'))
        	$task->duedate = strtotime($obj->getValue('deadline'));
		if ($obj->getValue('start_date'))
        	$task->startdate = strtotime($obj->getValue('start_date'));
        
        return $task;
	}

	/**
	 * Get an email message from ANT and convert to SyncObjet
	 *
	 * @param string $id The unique id of the message  to get
     * @param ContentParameters $contentparameters flag
	 * @return SyncMail
	 */
	public function getEmail($id, $contentparameters)
	{
		$truncsize = Utils::GetTruncSize($contentparameters->GetTruncation());
        $mimesupport = $contentparameters->GetMimeSupport();
		$bodypreference = $contentparameters->GetBodyPreference(); /* fmbiete's contribution r1528, ZP-320 */

		$msg = CAntObject::factory($this->dbh, "email_message", $id, $this->user);
		if (!$msg->id)
			return false;

		$output = new SyncMail();

		//Select body type preference
		$bpReturnType = SYNC_BODYPREFERENCE_PLAIN;
		if ($bodypreference !== false) {
			$bpReturnType = Utils::GetBodyPreferenceBestMatch($bodypreference); // changed by mku ZP-330
		}

		$htmlBody = $msg->getBody();
		$plainBody = $msg->getPlainTextBody();
		$htmlBody = str_replace("\n","\r\n", str_replace("\r","",$htmlBody));
        $plainBody = str_replace("\n","\r\n", str_replace("\r","",$plainBody));

		if (Request::GetProtocolVersion() >= 12.0) 
		{
			$output->asbody = new SyncBaseBody();

			switch($bpReturnType) 
			{
				case SYNC_BODYPREFERENCE_PLAIN:
					$output->asbody->data = $plainBody;
					break;
				case SYNC_BODYPREFERENCE_HTML:
					if ($htmlBody == "") 
					{
						$output->asbody->data = $plainBody;
						$bpReturnType = SYNC_BODYPREFERENCE_PLAIN;
					}
					else 
					{
						$output->asbody->data = $htmlBody;
					}
					break;
				case SYNC_BODYPREFERENCE_MIME:
					$output->asbody->data = $msg->getOriginal();
					break;
				case SYNC_BODYPREFERENCE_RTF:
					$output->asbody->data = base64_encode($plainBody);
					break;
			}

			// truncate body, if requested
			if(strlen($output->asbody->data) > $truncsize) {
				$output->asbody->data = Utils::Utf8_truncate($output->asbody->data, $truncsize);
				$output->asbody->truncated = 1;
			}

			$output->asbody->type = $bpReturnType;
			$output->nativebodytype = $bpReturnType;
			$output->asbody->estimatedDataSize = strlen($output->asbody->data);

			$bpo = $contentparameters->BodyPreference($output->asbody->type);
			if (Request::GetProtocolVersion() >= 14.0 && $bpo->GetPreview()) {
				$output->asbody->preview = Utils::Utf8_truncate(Utils::ConvertHtmlToText($plainBody), $bpo->GetPreview());
			}
			else 
			{
				$output->asbody->truncated = 0;
			}
		}
		/* END fmbiete's contribution r1528, ZP-320 */
		else 
		{ 
			// ASV_2.5
			$output->bodytruncated = 0;
			/* BEGIN fmbiete's contribution r1528, ZP-320 */
			if ($bpReturnType == SYNC_BODYPREFERENCE_MIME) 
			{
				$original = $msg->getOriginal();
				if (strlen($original) > $truncsize) 
				{
					$output->mimedata = Utils::Utf8_truncate($original, $truncsize);
					$output->mimetruncated = 1;
				}
				else {
					$output->mimetruncated = 0;
					$output->mimedata = $original;
				}
				$output->mimesize = strlen($output->mimedata);
			}
			else 
			{
				// truncate body, if requested
				if (strlen($plainBody) > $truncsize) 
				{
					$output->body = Utils::Utf8_truncate($plainBody, $truncsize);
					$output->bodytruncated = 1;
				}
				else {
					$output->body = $plainBody;
					$output->bodytruncated = 0;
				}
				$output->bodysize = strlen($output->body);
			}
			/* END fmbiete's contribution r1528, ZP-320 */
		}

		/*
		// truncate body, if requested
		if($truncsize && strlen($body) > $truncsize) 
		{
			$body = utf8_truncate($body, $truncsize);
			//$body = mb_substr($body, 0, $truncsize, "UTF-8");
			$output->bodytruncated = 1;
		} 
		else 
		{
			$body = $body;
			$output->bodytruncated = 0;
		}
		 */

		/*
		$orig = $msg->getOriginal();
		if($truncsize && strlen($orig) > $truncsize) 
		{
			$orig = utf8_truncate($orig, $truncsize);
			//$body = mb_substr($body, 0, $truncsize, "UTF-8");
			$output->mimetruncated = 1;
		} 
		else 
		{
			$orig = $orig;
			$output->mimetruncated = 0;
		}
		 */

		$datereceived = strtotime($msg->getValue("message_date"));
		$tz = TimezoneUtil::GetFullTZ();
		if (!$tz)
			$tz =  $this->getGMTTZ();
		//$cur_tz = date_default_timezone_get();
		//date_default_timezone_set('UTC');
		//$datereceived = time();
		//date_default_timezone_set($cur_tz);
		//$tz =  $this->getGMTTZ();
		
		// TODO: add these functions $msg->getOrigHeader(); $msg->getOrigBody();
		//$output->mimetruncated = if mime was larger than $trunksize and was truncated
		//$output->mimesize = sizeof($orig);
		//$output->mimedata = $orig;

		$output->bodysize = strlen($body);
		$output->body = $body;
		$output->timezone 	= base64_encode($this->getSyncBlobFromTZ($tz));
		$output->datereceived = $datereceived;
		$output->displayto = $msg->getValue("send_to");
		$output->importance = ($msg->getValue("priority")) ? preg_replace("/\D+/", "", $msg->getValue("priority")) : NULL;
		$output->messageclass = "IPM.Note";
		$output->subject = mb_convert_encoding($msg->getValue("subject"), "UTF-8", "UTF-7");
		$output->read = ($msg->getValue("flag_seen") == 't') ? 1 : 0;
		$output->to = $msg->getValue("send_to");
		$output->cc = $msg->getValue("cc");
		$output->from = $msg->getValue("sent_from");
		$output->reply_to = $msg->getValue("reply_to");
		//$output->threadtopic = ($msg->getValue("thread_topic")) ? $msg->getValue("thread_topic") : NULL;

		// Language Code Page ID: http://msdn.microsoft.com/en-us/library/windows/desktop/dd317756%28v=vs.85%29.aspx
		$output->internetcpid = INTERNET_CPID_UTF8;
		if (Request::GetProtocolVersion() >= 12.0) {
			$output->contentclass = "urn:content-classes:message";
		}

		// Attachments are not needed for MIME messages
		if ($bpReturnType != SYNC_BODYPREFERENCE_MIME)
		{
			// Attachments are only searched in the top-level part
			$attachments = $msg->getAttachments();
			if (count($attachments))
			{
				if (!isset($output->asattachments) || !is_array($output->asattachments))
					$output->asattachments = array();

				foreach ($attachments as $att)
				{
					if($att->getValue('disposition') == "attachment" || $att->getValue('disposition') == "inline") 
					{
						$attachment = new SyncAttachment();
						$attachment->attsize = $att->getValue('size');
						if($att->getValue('filename'))
							$attname = $att->getValue('filename');
						else if($att->getValue('name'))
							$attname = $att->getValue('name');
						//else if(isset($att['content-description']))
							//$attname = $att['content-description'];
						else $attname = "unknown attachment";

						$attachment->displayname = $attname;
						//$attachment->attname = $folderid . ":" . $id . ":" . $n;
						$attachment->attname = $att->getValue('id');
						$attachment->attmethod = 1;
						// For some reason the below totally broke the iphone, it has been fixed now
						//$attachment->attoid = isset($part->headers['content-id']) ? $part->headers['content-id'] : "";
						array_push($output->attachments, $attachment);
					}
				}
			}
		}

		return $output;
	}

	/** 
	 * This function is analogous to GetMessageList.
	 *
	 * @param string $parent_id	A possible parent of which this folder would be a child
	 * @param string $path_pre Used to set a prefix for subfolders
	 * @return SyncFolder[] list of folders with children
     */
	public function GetFolderList($parent_id = null, $path_pre = "", $excludeEmail=false) 
	{
        $folders = array();

		// Get email
		// ------------------------------------------------------
		if (!$excludeEmail)
		{
			if ($parent_id)
				$query = "select id, name from email_mailboxes where user_id='".$this->user->id."' and parent_box='$parent_id'";
			else
				$query = "select id, name from email_mailboxes where user_id='".$this->user->id."' and parent_box is null";

			$result = $this->dbh->Query($query);
			$num = $this->dbh->GetNumberRows($result);
			for ($i = 0; $i < $num; $i++)
			{
				$row = $this->dbh->GetRow($result, $i);

				$box = array();
				$box["id"] = imap_utf7_encode($path_pre.$row['name']); // $path_pre.$row['name'];
				$box["mod"] = imap_utf7_encode($row['name']);  // mod is last part of path
				$box["parent"] = ($path_pre) ? "0" : $path_pre; // parent is all previous parts of path

				$folders[]=$box;

				$subs = array();
				$subs = $this->GetFolderList($row['id'], $path_pre.$row['name'].".");

				if (count($subs))
				{
					$folders = array_merge($folders, $subs);
				}
			}
		}

		// All other folder types
		if (!$parent_id)
		{
			// Contacts
			// ------------------------------------------------------
			$box = array();
			$box["id"] = "contacts_root";
			$box["mod"] = "Contacts";  // mod is last part of path
			$box["parent"] = "0"; // parent is all previous parts of path
			$folders[]=$box;

			// Calendar
			// ------------------------------------------------------
			$box = array();
			$box["id"] = "calendar_root";
			$box["mod"] = "Calendar";  // mod is last part of path
			$box["parent"] = "0"; // parent is all previous parts of path
			$folders[]=$box;

			// Tasks
			// ------------------------------------------------------
			$box = array();
			$box["id"] = "tasks_root";
			$box["mod"] = "Tasks";  // mod is last part of path
			$box["parent"] = "0"; // parent is all previous parts of path
			$folders[]=$box;
		}

		return $folders;
    }

	/** 
	 * GetFolder should return an actual SyncFolder object with all the properties set. Folders
     * are pretty simple really, having only a type, a name, a parent and a server ID.
	 *
	 * $param string $id The id/name of the folder to get
	 * @return SyncFolder
     */
	public function GetFolder($id) 
	{
		debugLog("GetFolder $id");

        $folder = new SyncFolder();
        $folder->serverid = $id;

        // explode hierarchy
        $fhir = explode(".", $id);

        // compare on lowercase strings
        $lid = strtolower($id);

		switch ($lid)
		{
		case "inbox":
            $folder->parentid = "0"; // Root
            $folder->displayname = "Inbox";
            $folder->type = SYNC_FOLDER_TYPE_INBOX;
			break;
		case "drafts":
            $folder->parentid = "0";
            $folder->displayname = "Drafts";
            $folder->type = SYNC_FOLDER_TYPE_DRAFTS;
			break;
		case "trash":
            $folder->parentid = "0";
            $folder->displayname = "Trash";
            $folder->type = SYNC_FOLDER_TYPE_WASTEBASKET;
            $this->_wasteID = $id;
			break;
		case "sent":
		case "sent items":
            $folder->parentid = "0";
            $folder->displayname = "Sent";
            $folder->type = SYNC_FOLDER_TYPE_SENTMAIL;
            $this->_sentID = $id;
			break;
		case "contacts_root":
            $folder = new SyncFolder();
            $folder->serverid = $id;
            $folder->parentid = "0";
            $folder->displayname = "Contacts";
            $folder->type = SYNC_FOLDER_TYPE_CONTACT;
			break;
		case "calendar_root":
            $folder = new SyncFolder();
            $folder->serverid = $id;
            $folder->parentid = "0";
            $folder->displayname = "Calendar";
            $folder->type = SYNC_FOLDER_TYPE_APPOINTMENT;
			break;
		case "tasks_root":
            $folder = new SyncFolder();
            $folder->serverid = $id;
            $folder->parentid = "0";
            $folder->displayname = "Tasks";
            $folder->type = SYNC_FOLDER_TYPE_TASK;
			break;
		default:
			if (count($fhir) > 1)
			{
				//$folder->displayname = windows1252_to_utf8(utf8_decode(array_pop($fhir)));
				$folder->displayname = mb_convert_encoding(array_pop($fhir), "UTF-8", "UTF-7"); 
				$folder->parentid = implode(".", $fhir);
			}
			else 
			{
				//$folder->displayname = windows1252_to_utf8(utf8_decode($id));
				$folder->displayname = mb_convert_encoding($id, "UTF-8", "UTF-7");
				$folder->parentid = "0";
			}
			$folder->type = SYNC_FOLDER_TYPE_OTHER;
			break;
		}

        return $folder;
    }

	// ANT object setters/savers
	// ====================================================================================
	
	/**
	 * save a contact
	 *
	 * @param string $id If editing an object then this will be the id, otherwise null
	 * @param SyncContact A contact object containing the values from the device
	 * @return CAntObject
	 */
	public function saveContact($id, $contact)
	{
		$obj = new CAntObject($this->dbh, "contact_personal", $id, $this->user);
		$obj->setValue('user_id', $this->user->id);
		$obj->setValue('first_name', $contact->firstname);
		$obj->setValue('last_name', $contact->lastname);
		$obj->setValue('middle_name', $contact->middlename);
		$obj->setValue('phone_home', $contact->homephonenumber);
		$obj->setValue('phone_work', $contact->businessphonenumber);
		$obj->setValue('phone_cell', $contact->mobilephonenumber);
		$obj->setValue('phone_fax', $contact->homefaxnumber);
		$obj->setValue('phone_pager', $contact->pagernumber);
		$obj->setValue('email', $contact->email1address);
		$obj->setValue('email2', $contact->email2address);
		$obj->setValue('street', $contact->homestreet);
		$obj->setValue('city', $contact->homecity);
		$obj->setValue('state', $contact->homestate);
		$obj->setValue('zip', $contact->homepostalcode);
		$obj->setValue('company', $contact->companyname);
		$obj->setValue('job_title', $contact->jobtitle);
		$obj->setValue('website', $contact->webpage);
		$obj->setValue('notes', $contact->body);
		$obj->setValue('spouse_name', $contact->spouse);
		$obj->setValue('birthday', $contact->birthday);
		$obj->setValue('anniversary', $contact->anniversary);
		$obj->setValue('business_street', $contact->businessstreet);
		$obj->setValue('business_city', $contact->businesscity);
		$obj->setValue('business_state', $contact->businessstate);
		$obj->setValue('business_zip', $contact->businesspostalcode);
		if (isset($contact->picture)) 
		{
			//debugLog("PICTURE: ".$contact->picture);
			/*
           	$picbinary = base64_decode($contact->picture);
            $picsize = strlen($picbinary);

			if ($picsize)
			{
				$antfs = new CAntFs($this->dbh, $this->user);
				$fldr = $antfs->openFolder("%userdir%/Contact Files/$id", true);
				$file = $fldr->createFile("profilepic.jpg");
				$size = $file->write($picbinary);
				if ($file->id)
				{
					$obj->setValue('image_id', $file->id);
				}
			}
			 */
		}
		$cid = $obj->save();
		return $obj;
	}

	/**
	 * Save a task
	 *
	 * @param string $id If editing an object then this will be the id, otherwise null
	 * @param SyncTask A contact object containing the values from the device
	 * @return CAntObject
	 */
	public function saveTask($id, $task)
	{
        $obj = new CAntObject($this->dbh, "task", $id, $this->user);
        $obj->setValue('user_id', $this->user->id);
        $obj->setValue('name', $task->subject);
        $obj->setValue('notes', $task->body);
        $obj->setValue('start_date', date("Y-m-d", $task->startdate));
        $obj->setValue('deadline', date("Y-m-d", $task->duedate));        
        $tid = $obj->save();

        return $obj;
	}

	/**
	 * Save a note
	 *
	 * @param string $id If editing an object then this will be the id, otherwise null
	 * @param SyncNote A contact object containing the values from the device
	 * @return CAntObject
	 */
	public function saveNote($id, $note)
	{
        $obj = new CAntObject($this->dbh, "note", $id, $this->user);
        $obj->setValue('user_id', $this->user->id);
        $obj->setValue('name', $task->subject);
        $obj->setValue('body', $note->asbody);
        $obj->setValue('body_type', "plain");
        $tid = $obj->save();

        return $obj;
	}

	/**
	 * Save a message from the pim to ANT
	 *
	 * @param string $id If editing an object then this will be the id, otherwise null
	 * @param SyncMail A mail object containing the values from the device
	 * @return CAntObject
	 */
	public function saveEmailMessage($id, $mail)
	{
		$obj = CAntObject::factory($this->dbh, "email_message", $id, $this->user);
		$obj->setValue('subject', $mail->subject);
		$obj->setValue('to', $mail->to);
		$obj->setValue("owner_id", $this->user->id);
		$obj->setValue("flag_seen", ($mail->read) ? 't' : 'f');
		$mid = $obj->save();

		// TODO: we need to add the rest of the fields here including the mailbox

		return $obj;
	}

	/**
	 * Save calendar event
	 *
	 * @param string $id If editing an object then this will be the id, otherwise null
	 * @param SyncAppointment A contact object containing the values from the device
	 * @return CAntObject
	 */
	public function saveAppointment($id, $appointment)
	{
		// Skip birthdays
		if (strpos($appointment->subject, "' Birthday") !== false)
			return false;

		// Get timezone info
		$tzData = false;
        if(isset($appointment->timezone))
		{
            $tzData = $this->getTZFromSyncBlob(base64_decode($appointment->timezone));
			$tz = $tzData['name'];
		}

		if (!$tz && $this->user->timezoneName)
			$tz = $this->user->timezoneName;

		if ($tz)
		{
			//$cur_tz = date_default_timezone_get();
			$this->dbh->SetTimezone($tz);
			//date_default_timezone_set($tz);
		}

		//calculate duration because without it some webaccess views are broken. duration is in min
        $localstart = $this->getLocaltimeByTZ($appointment->starttime, $tz);
        $localend = $this->getLocaltimeByTZ($appointment->endtime, $tz);
        $duration = ($localend - $localstart)/60;

		//nokia sends an yearly event with 0 mins duration but as all day event,
        //so make it end next day
		if ($appointment->starttime == $appointment->endtime && isset($appointment->alldayevent) && $appointment->alldayevent) 
		{
            $duration = 1440;
            $appointment->endtime = $appointment->starttime + 24 * 60 * 60;
            $localend = $localstart + 24 * 60 * 60;
        }

		$antcal = new AntCalendar($this->dbh);

		$antEvent = CAntObject::factory($this->dbh, "calendar_event", $id, $this->user);
		$antEvent->setValue("name", $appointment->subject);
		$antEvent->setValue("location", $appointment->location);
		$antEvent->setValue("notes", $appointment->body);
		$antEvent->setValue("sharing", 1);
		$antEvent->setValue("user_id", $this->user->id);
		$antEvent->setValue("all_day", ($appointment->alldayevent) ? 't' : 'f');
		$antEvent->setValue("calendar", $antcal->getUserCalendar($this->user->id));
		if ($appointment->starttime)
			$antEvent->setValue("ts_start", date("Y-m-d g:i A", $localstart));
		if ($appointment->endtime)
			$antEvent->setValue("ts_end", date("Y-m-d g:i A", $localend));

		// 1 = private, 2 = public
		//$values['user_status'] 		= ($appointment->busystatus == 2) ? 1 : 3;
		//debugLog("\tbusystatus=".$appointment->busystatus);
		//$values['uid']	 		= $appointment->uid;
		//$values['organizername'] 	= $appointment->organizername;
		//$values['organizeremail']	= $appointment->organizeremail;
		//$values['sensitivity'] 	= $appointment->sensitivity;
		//$values['user_status'] 	= $appointment->busystatus;

		// Fix dates if all day. Apple will push to midnight of the next day...
		if ($appointment->alldayevent)
		{
			if (date("g:i A", $appointment->endtime == "12:00 AM"))
			{
				$antEvent->setValue("ts_end", date("Y-m-d", strtotime("-1 day", $appointment->endtime))." 11:59 PM");
			}
		}

		
		if (!$id) // Check for duplicates
		{
			/*
			if ($appointment->subject && $start_block && $end_block && $values['date_start'] && $values['date_end'])
			{
				$result = $this->dbh->Query("select id from calendar_events where calendar='".$values['calendar_id']."' and start_block='$start_block'
										and end_block='$end_block' and date_start='".$values['date_start']."' and date_end='".$values['date_end']."'
										and name='".$this->dbh->Escape($appointment->subject)."'");
				if ($this->dbh->GetNumberRows($result))
					$id = $this->dbh->GetValue($result, 0, "id");
			}
			 */
		}

		if(isset($appointment->recurrence)) 
		{
			$rp = $antEvent->getRecurrencePattern();
			$rp->type = RECUR_DAILY;

			if(!isset($appointment->recurrence->interval))
				$appointment->recurrence->interval = 1;
			
			$rp->interval = $appointment->recurrence->interval;
			$rp->dateStart = ($ts_start) ? date("Y-m-d", $ts_start) : date("Y-m-d");
			if ($appointment->recurrence->until)
				$rp->dateEnd = date("Y-m-d", $appointment->recurrence->until);

			switch($appointment->recurrence->type) 
			{
			// Daily
			case 0:
				$rp->type = RECUR_DAILY;
				break;

			// Weekly
			case 1:
				$rp->type = RECUR_WEEKLY;
				if ($appointment->recurrence->dayofweek & WEEKDAY_SUNDAY)
					$rp->dayOfWeekMask = $rp->dayOfWeekMask | WEEKDAY_SUNDAY;
				if ($appointment->recurrence->dayofweek & WEEKDAY_MONDAY)
					$rp->dayOfWeekMask = $rp->dayOfWeekMask | WEEKDAY_MONDAY;
				if ($appointment->recurrence->dayofweek & WEEKDAY_TUESDAY)
					$rp->dayOfWeekMask = $rp->dayOfWeekMask | WEEKDAY_TUESDAY;
				if ($appointment->recurrence->dayofweek & WEEKDAY_WEDNESDAY)
					$rp->dayOfWeekMask = $rp->dayOfWeekMask | WEEKDAY_WEDNESDAY;
				if ($appointment->recurrence->dayofweek & WEEKDAY_THURSDAY)
					$rp->dayOfWeekMask = $rp->dayOfWeekMask | WEEKDAY_THURSDAY;
				if ($appointment->recurrence->dayofweek & WEEKDAY_FRIDAY)
					$rp->dayOfWeekMask = $rp->dayOfWeekMask | WEEKDAY_FRIDAY;
				if ($appointment->recurrence->dayofweek & WEEKDAY_SATURDAY)
					$rp->dayOfWeekMask = $rp->dayOfWeekMask | WEEKDAY_SATURDAY;
				break;

			// Monthly
			case 2:
				$rp->type = RECUR_MONTHLY;
				$rp->dayOfMonth = $appointment->recurrence->dayofmonth;
				break;

			// Monthly(nth)
			case 3:
				$rp->type = RECUR_MONTHNTH;
				$rp->instance = $appointment->recurrence->weekofmonth;
				if ($appointment->recurrence->dayofweek & WEEKDAY_SUNDAY)
					$rp->dayOfWeekMask = $rp->dayOfWeekMask | WEEKDAY_SUNDAY;
				if ($appointment->recurrence->dayofweek & WEEKDAY_MONDAY)
					$rp->dayOfWeekMask = $rp->dayOfWeekMask | WEEKDAY_MONDAY;
				if ($appointment->recurrence->dayofweek & WEEKDAY_TUESDAY)
					$rp->dayOfWeekMask = $rp->dayOfWeekMask | WEEKDAY_TUESDAY;
				if ($appointment->recurrence->dayofweek & WEEKDAY_WEDNESDAY)
					$rp->dayOfWeekMask = $rp->dayOfWeekMask | WEEKDAY_WEDNESDAY;
				if ($appointment->recurrence->dayofweek & WEEKDAY_THURSDAY)
					$rp->dayOfWeekMask = $rp->dayOfWeekMask | WEEKDAY_THURSDAY;
				if ($appointment->recurrence->dayofweek & WEEKDAY_FRIDAY)
					$rp->dayOfWeekMask = $rp->dayOfWeekMask | WEEKDAY_FRIDAY;
				if ($appointment->recurrence->dayofweek & WEEKDAY_SATURDAY)
					$rp->dayOfWeekMask = $rp->dayOfWeekMask | WEEKDAY_SATURDAY;
				
				break;

			// Yearly
			case 5:
				$rp->type = RECUR_YEARLY;
				$rp->dayOfWeekMask = $appointment->recurrence->dayofmonth;
				$rp->monthOfYear = $appointment->recurrence->monthofyear;
				break;

			// YearlyNth
			case 6:
				$values["recur_type"] = RECUR_YEARNTH;
				$rp->dayOfWeekMask = $appointment->recurrence->dayofmonth;
				if ($appointment->recurrence->dayofweek & WEEKDAY_SUNDAY)
					$rp->dayOfWeekMask = $rp->dayOfWeekMask | WEEKDAY_SUNDAY;
				if ($appointment->recurrence->dayofweek & WEEKDAY_MONDAY)
					$rp->dayOfWeekMask = $rp->dayOfWeekMask | WEEKDAY_MONDAY;
				if ($appointment->recurrence->dayofweek & WEEKDAY_TUESDAY)
					$rp->dayOfWeekMask = $rp->dayOfWeekMask | WEEKDAY_TUESDAY;
				if ($appointment->recurrence->dayofweek & WEEKDAY_WEDNESDAY)
					$rp->dayOfWeekMask = $rp->dayOfWeekMask | WEEKDAY_WEDNESDAY;
				if ($appointment->recurrence->dayofweek & WEEKDAY_THURSDAY)
					$rp->dayOfWeekMask = $rp->dayOfWeekMask | WEEKDAY_THURSDAY;
				if ($appointment->recurrence->dayofweek & WEEKDAY_FRIDAY)
					$rp->dayOfWeekMask = $rp->dayOfWeekMask | WEEKDAY_FRIDAY;
				if ($appointment->recurrence->dayofweek & WEEKDAY_SATURDAY)
					$rp->dayOfWeekMask = $rp->dayOfWeekMask | WEEKDAY_SATURDAY;
				break;

			default: // not supported
				return false;
				break;
			}

			// Process exceptions. The PDA will send all exceptions for this recurring item.
			/*
			if(isset($appointment->exceptions)) 
			{
				foreach($appointment->exceptions as $exception) 
				{
					// we always need the base date
					if(!isset($exception->exceptionstarttime))
						continue;

					if(isset($exception->deleted) && $exception->deleted) 
					{
						// Delete exception
						if(!isset($recur["deleted_occurences"]))
							$recur["deleted_occurences"] = array();

						array_push($recur["deleted_occurences"], $this->_getDayStartOfTimestamp($exception->exceptionstarttime));
					} 
					else 
					{
						// Change exception
						$mapiexception = array("basedate" => $this->_getDayStartOfTimestamp($exception->exceptionstarttime));

						if(isset($exception->starttime))
							$mapiexception["start"] = $this->_getLocaltimeByTZ($exception->starttime, $tz);

						if(isset($exception->endtime))
							$mapiexception["end"] = $this->_getLocaltimeByTZ($exception->endtime, $tz);

						if(isset($exception->subject))
							$mapiexception["subject"] = u2w($exception->subject);

						if(isset($exception->location))
							$mapiexception["location"] = u2w($exception->location);

						if(isset($exception->busystatus))
							$mapiexception["busystatus"] = $exception->busystatus;

						if(isset($exception->reminder)) 
						{
							$mapiexception["reminder_set"] = 1;
							$mapiexception["remind_before"] = $exception->reminder;
						}

						if(isset($exception->alldayevent))
							$mapiexception["alldayevent"] = $exception->alldayevent;

						if(!isset($recur["changed_occurences"]))
							$recur["changed_occurences"] = array();

						array_push($recur["changed_occurences"], $mapiexception);
					}
				}
			}
			 */

			//debugLog("setAppointment recurrance - ".var_export($appointment->recurrence, true));
		}

		$eid = $antEvent->save();

		return $antEvent;
	}

	/**
	 * Delete task
	 *
	 * @param int $id The id of the task to delete
	 */
	public function deleteTask($id)
	{
		$obj = CAntObject::factory($this->dbh, "task", $id, $this->user);
		return $obj->remove();
	}

	/**
	 * Delete contact
	 *
	 * @param int $id The id of the contact to delete
	 */
	public function deleteContact($id)
	{
		$obj = CAntObject::factory($this->dbh, "contact_personal", $id, $this->user);
		return $obj->remove();
	}

	/**
	 * Delete calendar event
	 *
	 * @param int $id The id of the item  to delete
	 */
	public function deleteAppointment($id)
	{
		$obj = CAntObject::factory($this->dbh, "calendar_event", $id, $this->user);
		return $obj->remove();
	}

	/**
	 * Delete note
	 *
	 * @param int $id The id of the item  to delete
	 */
	public function deleteNote($id)
	{
		$obj = CAntObject::factory($this->dbh, "note", $id, $this->user);
		return $obj->remove();
	}

	/**
	 * Delete email message
	 *
	 * @param int $id The id of the item  to delete
	 */
	public function deleteEmailMessage($id)
	{
		$obj = CAntObject::factory($this->dbh, "email_message", $id, $this->user);
		return $obj->remove();
	}

	/**
	 * Mark an email message as read
	 *
	 * @param string $id If editing an object then this will be the id, otherwise null
	 * @param SyncMail A mail object containing the values from the device
	 * @return CAntObject
	 */
	public function markEmailMessageRead($id, $seen=true)
	{
		$obj = CAntObject::factory($this->dbh, "email_message", $id, $this->user);
		$obj->setValue('flag_seen', ($seen) ? 't' : 'f');
		$mid = $obj->save();

		return $obj;
	}

	/**
	 * Mark an email message as read
	 *
	 * @param string $id If editing an object then this will be the id, otherwise null
	 * @param SyncMail A mail object containing the values from the device
	 * @return bool
	 */
	public function moveEmailMessage($id, $newFolder)
	{
		$newFolder = str_replace('.', '/', $newFolder); // Convert from active sync/imap to Netric path
		$obj = CAntObject::factory($this->dbh, "email_message", $id, $this->user);
		$grp = $obj->getGroupingEntryByPath("mailbox_id", $newFolder);
		if ($grp['id'])
		{
			$obj->move($grp['id']);
			return true;
		}
		else
		{
			return false;
		}
	}

	// Backend utility functions
	// ====================================================================================

	/**
	 * Get AntObjectSync partnership collection based on folder id
	 *
	 * @param string $folderid The folder we are synchronizing
	 * @return AntObjectSync_Collection
	 */
	public function getSyncCollection($folderid) 
	{
		$objType = "";
		$parent = null;
		$cond = array();

		if (!$this->partnership && $this->deviceId)
        {
            $serviceManager = ServiceLocatorLoader::getInstance($this->dbh)->getServiceManager();
            $entitySync = $serviceManager->get("EntitySync");
            $this->partnership = $entitySync->getPartner($this->deviceId);
            if (!$this->partnership)
            {
                $this->partnership = $entitySync->createPartner($this->deviceId, $this->user->id);
            }
        }

		if (!$this->partnership)
			throw new StatusException("AntBackend::getSyncCollection(): Could not create partnership");

		// Check if we have already loaded the collection
		if ($this->syncCollections[$folderid])
			return $this->syncCollections[$folderid];

		// get object collection
		switch ($folderid)
		{
		case "contacts_root":
			$objType = "contact_personal";
			$cond = array(array("blogic"=>"and", "field"=>"user_id", "operator"=>"is_equal", "condValue"=>$this->user->id));
			break;

		case "calendar_root":
			$objType = "calendar_event";
			$cal = $this->user->getDefaultCalendar();
			$cond = array(array("blogic"=>"and", "field"=>"calendar", "operator"=>"is_equal", "condValue"=>$cal->id));
			break;

		case "tasks_root":
			$objType = "task";
			$cond = array(array("blogic"=>"and", "field"=>"user_id", "operator"=>"is_equal", "condValue"=>$this->user->id));
			break;

		default:
			$objType = "email_message";
			$parent = "mailbox_id";
			$cond = array(
				array("blogic"=>"and", "field"=>"owner_id", "operator"=>"is_equal", "condValue"=>$this->user->id),
                array("blogic"=>"and", "field"=>"mailbox_id", "operator"=>"is_equal", "condValue"=>$folderid),
			);
			break;
		}

		$coll = $this->partnership->getEntityCollection($objType, $cond);
        if (!$coll)
        {
            $serviceManager = ServiceLocatorLoader::getInstance($this->dbh)->getServiceManager();
            $coll = \Netric\EntitySync\Collection\CollectionFactory::create(
                $serviceManager,
                \Netric\EntitySync\EntitySync::COLL_TYPE_ENTITY
            );
            $coll->setObjType($objType);
            $coll->setConditions($conditions);
            $this->partnership->addCollection($coll);
            $serviceManager->get("EntitySync_DataMapper")->savePartner($this->partnership);
        }

        // Cache for later
        $this->syncCollections[$folderid] = $coll;

        return $coll;
    }

	/**
     * Returns an GMT timezone array
     *
     * @return array
     */
    public function getGMTTZ() {
        $tz = array(
            "bias" => 0,
            "tzname" => "",
            "dstendyear" => 0,
            "dstendmonth" => 10,
            "dstendday" => 0,
            "dstendweek" => 5,
            "dstendhour" => 2,
            "dstendminute" => 0,
            "dstendsecond" => 0,
            "dstendmillis" => 0,
            "stdbias" => 0,
            "tznamedst" => "",
            "dststartyear" => 0,
            "dststartmonth" => 3,
            "dststartday" => 0,
            "dststartweek" => 5,
            "dststarthour" => 1,
            "dststartminute" => 0,
            "dststartsecond" => 0,
            "dststartmillis" => 0,
            "dstbias" => -60
    	);

        return $tz;
    }

	/**
     * Unpack timezone info from Sync
     *
     * @param string    $data
     *
     * @access private
     * @return array
     */
    public function getTZFromSyncBlob($data) {
        $tz = unpack(   "lbias/a64tzname/vdstendyear/vdstendmonth/vdstendday/vdstendweek/vdstendhour/vdstendminute/vdstendsecond/vdstendmillis/" .
                        "lstdbias/a64tznamedst/vdststartyear/vdststartmonth/vdststartday/vdststartweek/vdststarthour/vdststartminute/vdststartsecond/vdststartmillis/" .
                        "ldstbias", $data);

        // Make the structure compatible with class.recurrence.php
        $tz["timezone"] = $tz["bias"];
        $tz["timezonedst"] = $tz["dstbias"];

		// If not set, then use the users timezone
		if (!$tz['name'] && $this->user->timezoneName)
			$tz['name'] = $this->user->timezoneName;

        return $tz;
	}


	/**
     * Pack timezone info for Sync
     *
     * @param array     $tz
     *
     * @access private
     * @return string
     */
    public function getSyncBlobFromTZ($tz) {
        // set the correct TZ name (done using the Bias)
        if (!isset($tz["tzname"]) || !$tz["tzname"] || !isset($tz["tznamedst"]) || !$tz["tznamedst"])
            $tz = TimezoneUtil::FillTZNames($tz);

        $packed = pack("la64vvvvvvvv" . "la64vvvvvvvv" . "l",
                $tz["bias"], $tz["tzname"], 0, $tz["dstendmonth"], $tz["dstendday"], $tz["dstendweek"], $tz["dstendhour"], $tz["dstendminute"], $tz["dstendsecond"], $tz["dstendmillis"],
                $tz["stdbias"], $tz["tznamedst"], 0, $tz["dststartmonth"], $tz["dststartday"], $tz["dststartweek"], $tz["dststarthour"], $tz["dststartminute"], $tz["dststartsecond"], $tz["dststartmillis"],
                $tz["dstbias"]);

        return $packed;
	}

	/**
     * Checks the date to see if it is in DST, and returns correct GMT date accordingly
     *
     * @param long      $localtime
     * @param array     $tz
     *
     * @access private
     * @return long
     */
    public function getGMTTimeByTZ($localtime, $tz) {
        if(!isset($tz) || !is_array($tz))
            return $localtime;

        if($this->isDST($localtime, $tz))
            return $localtime + $tz["bias"]*60 + $tz["dstbias"]*60;
        else
            return $localtime + $tz["bias"]*60;
    }

    /**
     * Returns the local time for the given GMT time, taking account of the given timezone
     *
     * @param long      $gmttime
     * @param array     $tz
     *
     * @access private
     * @return long
     */
    public function getLocaltimeByTZ($gmttime, $tz) {
        if(!isset($tz) || !is_array($tz))
            return $gmttime;

        if($this->isDST($gmttime - $tz["bias"]*60, $tz)) // may bug around the switch time because it may have to be 'gmttime - bias - dstbias'
            return $gmttime - $tz["bias"]*60 - $tz["dstbias"]*60;
        else
            return $gmttime - $tz["bias"]*60;
    }

    /**
     * Returns TRUE if it is the summer and therefore DST is in effect
     *
     * @param long      $localtime
     * @param array     $tz
     *
     * @access private
     * @return boolean
     */
    public function isDST($localtime, $tz) {
        if( !isset($tz) || !is_array($tz) ||
            !isset($tz["dstbias"]) || $tz["dstbias"] == 0 ||
            !isset($tz["dststartmonth"]) || $tz["dststartmonth"] == 0 ||
            !isset($tz["dstendmonth"]) || $tz["dstendmonth"] == 0)
            return false;

        $year = gmdate("Y", $localtime);
        $start = $this->getTimestampOfWeek($year, $tz["dststartmonth"], $tz["dststartweek"], $tz["dststartday"], $tz["dststarthour"], $tz["dststartminute"], $tz["dststartsecond"]);
        $end = $this->getTimestampOfWeek($year, $tz["dstendmonth"], $tz["dstendweek"], $tz["dstendday"], $tz["dstendhour"], $tz["dstendminute"], $tz["dstendsecond"]);

        if($start < $end) {
            // northern hemisphere (july = dst)
          if($localtime >= $start && $localtime < $end)
              $dst = true;
          else
              $dst = false;
        } else {
            // southern hemisphere (january = dst)
          if($localtime >= $end && $localtime < $start)
              $dst = false;
          else
              $dst = true;
        }

        return $dst;
    }

	/**
	 * Get local timezone object
	public function getLocalTzObj() 
	{
		$tz = new DateTimeZone($this->user->timezoneName);
		$isDst = $this->timezoneDoesDST($tz);
		$isDstNow = date("I", time());
		if ($isDst)
		{
			$offset = (int)$tz->getOffset(new DateTime("now", $tz))/60;
			$offset = (-1 * $offset);
			if ($isDstNow==1)
				$offset = $offset + 60;
			//$date_dst_start = strtotime("Second Sunday March 0");  
			//$date_dst_end = strtotime("First Sunday November 0");  
		}
		else
		{
			$offset = $tz->getOffset(new DateTime("now", $tz))/60;
			$offset = (-1 * $offset);
		}

		$tzObject = array();
        $tzObject["bias"]             = $offset;
        $tzObject["name"]             = $tz->getName();
        //$tzObject["stdname"]          = ''; // $tz->getName()
        $tzObject["dstendyear"]       = 0;
        $tzObject["dstendmonth"]      = 11; //(isset($date_dst_end)) ?  date("m", $date_dst_end): 0;
        $tzObject["dstendday"]        = 0; //(isset($date_dst_end)) ?  date("d", $date_dst_end) : 0;
        $tzObject["dstendweek"]       = 1; //(isset($date_dst_end)) ?  date("W", $date_dst_end) : 0;
        $tzObject["dstendhour"]       = 2;
        $tzObject["dstendminute"]     = 0;
        $tzObject["dstendsecond"]     = 0;
        $tzObject["dstendmillis"]     = 0;
        $tzObject["stdbias"]          = 0;
        //$tzObject["dstname"]          = '';
        $tzObject["dststartyear"]     = 0;
        $tzObject["dststartmonth"]    = 3; //(isset($date_dst_start)) ?  date("m", $date_dst_start): 0;
        $tzObject["dststartday"]      = 0; //(isset($date_dst_start)) ?  date("d", $date_dst_start) : 0;
        $tzObject["dststartweek"]     = 2; //(isset($date_dst_start)) ?  date("W", $date_dst_start) : 0;
        $tzObject["dststarthour"]     = 2;
        $tzObject["dststartminute"]   = 0;
        $tzObject["dststartsecond"]   = 0;
        $tzObject["dststartmillis"]   = 0;
		$tzObject["dstbias"]          = -60;

        //if ($tzObject["dstendweek"] == -1 ) $tzObject["dstendweek"] = 5;
        //if ($tzObject["dststartweek"] == -1 ) $tzObject["dststartweek"] = 5;

        // Make the structure compatible with class.recurrence.php
        $tzObject["timezone"] = $tzObject["bias"];
        $tzObject["timezonedst"] = $tzObject["dstbias"];
        
		return $tzObject;
	}
	 */

    /**
     * Returns the local timestamp for the $week'th $wday of $month in $year at $hour:$minute:$second
     *
     * @param int       $year
     * @param int       $month
     * @param int       $week
     * @param int       $wday
     * @param int       $hour
     * @param int       $minute
     * @param int       $second
     *
     * @access private
     * @return long
     */
    public function getTimestampOfWeek($year, $month, $week, $wday, $hour, $minute, $second) {
        if ($month == 0)
            return;

        $date = gmmktime($hour, $minute, $second, $month, 1, $year);

        // Find first day in month which matches day of the week
        while(1) {
            $wdaynow = gmdate("w", $date);
            if($wdaynow == $wday)
                break;
            $date += 24 * 60 * 60;
        }

        // Forward $week weeks (may 'overflow' into the next month)
        $date = $date + $week * (24 * 60 * 60 * 7);

        // Reverse 'overflow'. Eg week '10' will always be the last week of the month in which the
        // specified weekday exists
        while(1) {
            $monthnow = gmdate("n", $date); // gmdate returns 1-12
            if($monthnow > $month)
                $date = $date - (24 * 7 * 60 * 60);
            else
                break;
        }

        return $date;
    }

    /**
     * Normalize the given timestamp to the start of the day
     *
     * @param long      $timestamp
     *
     * @access private
     * @return long
     */
    public function getDayStartOfTimestamp($timestamp) {
        return $timestamp - ($timestamp % (60 * 60 * 24));
    }

	/**
	 * Parse the message and return only the plaintext body
	 *
	 * @param mimeDecode $message The message that has been parsed into a mimeDecode object
	 * @param string $subtype The subtype to get, usually 'html' or 'plain'
     */
	private function getBody($message, $subtype="plain") 
	{
		/*
        $body = "";
        $htmlbody = "";

    	$this->getBodyRecursive($message, "html", $body);

		if(!isset($body) || $body === "") 
		{
        	$this->getBodyRecursive($message, "plain", $body);
            // HTML conversion goes here
        }

        return $body;
		 */
		$body = "";
        $htmlbody = "";

        $this->getBodyRecursive($message, $subtype, $body);

		if(!isset($body) || $body === "") 
		{
			$fallback = ($subtype == "html") ? "plain" : "html";

            $this->getBodyRecursive($message, $fallback, $body);

			// Convert to plain if needed
			if ($fallback == "html")
			{
				// remove css-style tags
				$body = preg_replace("/<style.*?<\/style>/is", "", $body);
				// remove all other html
				$body = strip_tags($body);
			}
        }

        return $body;
    }

   	/**
	 * Get all parts in the message with specified type and concatenate them together, unless the
     * Content-Disposition is 'attachment', in which case the text is apparently an attachment
	 */
	private function getBodyRecursive($message, $subtype, &$body) 
	{
        if(strcasecmp($message->ctype_primary,"text")==0 && strcasecmp($message->ctype_secondary,$subtype)==0 && isset($message->body))
            $body .= $message->body;

		if(strcasecmp($message->ctype_primary,"multipart")==0) 
		{
			if (is_array($message->parts))
			{
				foreach($message->parts as $part) 
				{
					if(!isset($part->disposition) || strcasecmp($part->disposition,"attachment"))  
					{
						$this->getBodyRecursive($part, $subtype, $body);
					}
				}
			}
        }
    }

    /**
     * Parses an mimedecode address array back to a simple "," separated string
     *
     * @param array         $ad             addresses array
     *
     * @access protected
     * @return string       mail address(es) string
     */
    protected function parseAddr($ad) {
        $addr_string = "";
        if (isset($ad) && is_array($ad)) {
            foreach($ad as $addr) {
                if ($addr_string) $addr_string .= ",";
                    $addr_string .= $addr->mailbox . "@" . $addr->host;
            }
        }
        return $addr_string;
    }
}
