<?php
/**
 * @author Sky Stebnicki, sky.stebnicki@aereus.com
 * @copyright Copyright (c) 2016 Aereus Corporation (http://www.aereus.com)
 */
namespace Netric\Mail;

use Netric\EntitySync\Collection\CollectionFactoryInterface;
use Netric\EntitySync\Collection\CollectionInterface;
use Netric\EntitySync\Partner;
use Netric\Error\AbstractHasErrors;
use Netric\Entity\ObjType\EmailAccountEntity;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntitySync\EntitySync;
use Netric\EntityQuery\Where;
use Netric\Log;
use Netric\EntityGroupings\Loader as GroupingsLoader;
use Netric\Mail\Storage;
use Netric\Mail\Storage\AbstractStorage;
use Netric\Mail\Storage\Imap;
use Netric\Mail\Storage\Pop3;
use Netric\EntityLoader;
use Netric\Mail\Storage\Writable\WritableInterface;

/**
 * Service responsible for receiving messages and synchronizing with remote mailboxes
 */
class ReceiverService extends AbstractHasErrors
{
    /**
     * Log
     *
     * @var Log
     */
    private $log = null;

    /**
     * Entity sync service
     *
     * @var EntitySync
     */
    private $entitySync = null;

    /**
     * The currently logged in user
     *
     * @var UserEntity
     */
    private $user = null;

    /**
     * Collection factory
     *
     * @var CollectionFactoryInterface
     */
    private $collectionFactory = null;

    /**
     * Entity groupings loader
     *
     * @var GroupingsLoader
     */
    private $groupingsLoader = null;

    /**
     * Entity loader
     *
     * @var EntityLoader
     */
    private $entityLoader = null;

    /**
     * Construct the transport service
     *
     * @param Log $log
     * @param UserEntity $user The currently logged in user
     * @param EntitySync $entitySync Sync Service
     * @param CollectionFactoryInterface $collectionFactory Factory for constructing collections
     * @param EntityLoader $entityLoader Loader to get and save messages
     * @param GroupingsLoader $groupingsLoader For loading mailbox groupings
     */
    public function __construct(
        Log $log,
        UserEntity $user,
        EntitySync $entitySync,
        CollectionFactoryInterface $collectionFactory,
        EntityLoader $entityLoader,
        GroupingsLoader $groupingsLoader
    ) {
        $this->log = $log;
        $this->user = $user;
        $this->entitySync = $entitySync;
        $this->collectionFactory = $collectionFactory;
        $this->entityLoader = $entityLoader;
        $this->groupingsLoader = $groupingsLoader;
    }

    /**
     * Synchronize a mailbox with a remote server
     *
     * @param int $mailboxId The id of the mailbox we are synchronizing
     * @param EmailAccountEntity $emailAccount The email account to sync
     * @return bool true on sucess, false on failure
     */
    public function syncMailbox($mailboxId, EmailAccountEntity $emailAccount)
    {
        // When syncing emails, account type should not be empty
        if(empty($emailAccount->getValue("type"))) {
            $this->log->info("ReceiverService->syncMail: Account has no type - " . $emailAccount->getId());
            return false;
        }

        // Get the mailbox path
        $mailboxGroupings = $this->groupingsLoader->get(
            "email_message", "maibox_id", ["user_id"=>$this->user->getId()]
        );
        $mailboxPath = $mailboxGroupings->getpath($mailboxId);

        // Right now we only want to synchronize the Inbox - Sky
        if (strtolower($mailboxPath) != "inbox") {
            $this->log->warning("ReceiverService->syncMail: $mailboxId is not an inbox and we only support inbox");
            return false;
        }

        // Get mail server connection
        $mail = $this->getMailConnection($emailAccount);

        // Get object sync partnership and collection
        $syncPartner = $this->entitySync->getPartner("EmailAccounts/" . $emailAccount->getId());
        $syncColl = $this->getSyncCollection($syncPartner, $emailAccount->getId(), $mailboxId);

        // First send changes to server
        $this->sendChanges($syncColl, $mail);

        // Now get new messages from the server and import
        $this->receiveChanges($syncColl);

        // Save the changes to the collection
        $this->entitySync->savePartner($syncPartner);

        // Close the mail connection
        $mail->close();

        return true;
    }

    /**
     * Send local changes to the server
     *
     * @param CollectionInterface $syncColl
     * @param AbstractStorage $mailServer Current mail server connection
     */
    private function sendChanges(CollectionInterface $syncColl, AbstractStorage $mailServer)
    {
        while (count($stats = $syncColl->getExportChanged(false)) > 0) {
            foreach ($stats as $stat) {

                // Load the email entity
                $emailEntity = $this->entityLoader->get("email_message", $stat['id']);
                $msgNum = $mailServer->getNumberByUniqueId($emailEntity->getValue("message_uid"));

                switch ($stat['action']) {
                    case 'change':

                        if ($mailServer instanceof WritableInterface) {

                            // Handle seen flag
                            if ($emailEntity->getValue("flag_seen") === true) {
                                $mailServer->setFlags($msgNum, Storage::FLAG_SEEN);
                            } else {
                                $mailServer->setFlags($msgNum, Storage::FLAG_UNSEEN);
                            }

                            // Handle flagged flag
                            if ($emailEntity->getValue("flag_flagged") === true) {
                                $mailServer->setFlags($msgNum, Storage::FLAG_FLAGGED);
                            } else {
                                $mailServer->setFlags($msgNum, Storage::FLAG_PASSED);
                            }

                            $this->log->debug("Exported: change:{$stat['id']}:{$emailEntity->getValue("commit_id")}");

                        } else {
                            // Log that this mail server does not support writing changes
                            $this->log->debug("Skipping export because server does not support WritableInterface: {$stat['id']}");
                        }

                        // Log that we exported this change so we never try to export it again
                        $syncColl->logExported($stat['id'], $emailEntity->getValue("commit_id"));
                        break;

                    case 'delete':
                        $mailServer->removeMessage($msgNum);
                        $syncColl->logExported($stat['id'], null);
                        $this->log->debug("Exported: delete:{$stat['id']}");
                        break;

                    default:
                        // An action was sent that we do not know how to handle
                        throw new \RuntimeException("Sync action {$stat['action']} is not handled!");
                }

                // Export last commit so we don't try to re-sync these changes next time
                if ($emailEntity->getValue("commit_id")) {
                    $syncColl->setLastCommitId($emailEntity->getValue("commit_id"));
                } else if ($syncColl->getId()) {
                    // If not permanently deleted then throw exception without commit id
                    throw new \RuntimeException(
                        "Tried to synchronize an email_message without a commit id: " .
                        $syncColl->getId()
                    );
                }

                // Check for error
                $error = $mailServer->getLastError();
                if ($error) {
                    $this->log->error("ReceiverService->sendChanges: " . $error->getMessage());
                }
            }
        }
    }

    /**
     * Get changes from a remote server and sync them locally
     *
     * @param CollectionInterface $syncColl
     * @param AbstractStorage $mailServer Current mail server connection
     */
    private function receiveChanges(CollectionInterface $syncColl, AbstractStorage $mailServer)
    {
        $importList = array();
        foreach($mailServer as $id=>$message) {
            $importList[] = array(
                "remote_id" => $mailServer->getUniqueId($id),
                "remote_revision"=>1,
                "message" => $message
            );
        }

        $stats = $syncColl->getImportChanged($importList);

        // $stat = array('remote_id', 'remote_revision', 'local_id', 'action', 'local_revision')
        foreach ($stats as $stat)
        {
            switch ($stat['action'])
            {
                case 'change':
                    // Set email meta data from server list
                    $message = null;
                    foreach ($importList as $toImport)
                    {
                        if ($toImport['remote_id'] == $stat['remote_id'])
                        {
                            $message = $toImport['message'];
                            break; // stop the loop
                        }
                    }

                    // Set return variable for keeping track of import
                    $importMid = 0;

                    if (isset($stat['local_id']))
                    {
                        $emailEntity = $this->entityLoader->get("email_message", $stat['local_id']);
                        $emailEntity->setValue("flag_seen", $message->hasFlag(Storage::FLAG_SEEN) ? true : false);
                        $emailEntity->setValue("flag_flagged", $message->hasFlag(Storage::FLAG_FLAGGED) ? true : false);
                        if ($emailEntity->fieldValueChanged("flag_seen") || $emailEntity->fieldValueChanged("flag_flagged"))
                        {
                            $this->entityLoader->save($emailEntity);
                            $this->log->info("ReceiverService->receiveChanges: Imported change {$stat['local_id']}");
                        }
                        else
                        {
                            $importMid = $stat['local_id'];
                        }
                    }
                    else
                    {
                        $importMid = $this->importMessage($message, $accountObj, $mailboxId);
                        $this->log->info("ReceiverService->receiveChanges: Imported new $mid");

                    }

                    if ($importMid > 0)
                    {
                        $emailObj = CAntObject::factory($this->dbh, "email_message", $mid, $this->user);
                        $syncColl->logImported($stat['remote_id'], $stat['remote_revision'], $importMid, $emailObj->revision);
                        echo "This was already imported earlier: $mid\n";

                        /*
                         * Routine to clean-up bugs in the old sync system where moves and deletes were not being sent
                         * to the server. This should eventually be removed but for now it can be used to clean-up
                         * imap inboxes.
                         * - Sky Stebnicki <sky.stebnicki@aereus.com>, 3/2/2015
                         *

                        // Check undeleted
                        $list = new CAntObjectList($this->dbh, "email_message");
                        $list->addCondition("and", "id", "is_not_equal", $mid);
                        $list->addCondition("and", "message_id", "is_equal", $emailObj->getValue('message_id'));
                        $list->addCondition("and", "message_date", "is_equal", $emailObj->getValue('message_date'));
                        $list->addCondition("and", "email_account", "is_equal", $accountObj->id);
                        $list->addCondition("and", "subject", "is_equal", $emailObj->getValue('subject'));
                        $list->addCondition("and", "owner_id", "is_equal", $emailObj->getValue('owner_id')); // just to be safe
                        $list->addCondition("and", "f_deleted", "is_equal", "f");
                        $list->getObjects();
                        if ($list->getNumObjects() > 0)
                            $emailObj->remove();

                        // Check deleted
                        $list = new CAntObjectList($this->dbh, "email_message");
                        $list->addCondition("and", "id", "is_not_equal", $mid);
                        $list->addCondition("and", "message_id", "is_equal", $emailObj->getValue('message_id'));
                        $list->addCondition("and", "message_date", "is_equal", $emailObj->getValue('message_date'));
                        $list->addCondition("and", "email_account", "is_equal", $accountObj->id);
                        $list->addCondition("and", "subject", "is_equal", $emailObj->getValue('subject'));
                        $list->addCondition("and", "owner_id", "is_equal", $emailObj->getValue('owner_id')); // just to be safe
                        $list->addCondition("and", "f_deleted", "is_equal", "t");
                        $list->getObjects();
                        if ($list->getNumObjects() > 0)
                            $emailObj->remove();
                        */
                    }
                    else if ($importMid == -1)
                    {
                        echo "Deleting stale imported message...\n";
                        // This message was previously imported and then deleted so delete on the server
                        $backend->processUpsync($mailboxPath, $stat['remote_id'], "deleted", null);
                        $syncColl->logImported($stat['remote_id']);
                    }
                    else if (0 == $importMid)
                    {
                        // If there was an error it $this->importEmail will return zero which
                        // will do nothing. This will cause the system to try again nex time
                        AntLog::getInstance()->error("Error trying to import [" . $accountObj->emailAddress . "]:" . var_export($emailMeta, true));
                    }

                    break;

                case 'delete':
                    echo "Imported delete {$stat['local_id']}\n";
                    if (isset($stat['local_id']) && $backend->isTwoWaySync())
                    {
                        $emailObj = CAntObject::factory($this->dbh, "email_message", $stat['local_id'], $this->user);
                        if ($emailObj->getValue("f_deleted") != 't')
                            $emailObj->remove();

                        $ret[] = $stat['local_id'];
                    }

                    $syncColl->logImported($stat['remote_id']);

                    break;
            }
        }
    }

    /**
     * Get an entity sync collection
     *
     * @param Partner $syncPartner The sync parter representing the email account
     * @param $accountId
     * @param $mailboxId
     * @return CollectionInterface
     * @throws \Exception
     */
    private function getSyncCollection(Partner $syncPartner, $accountId, $mailboxId)
    {
        if (!$syncPartner) {
            $syncPartner = $this->entitySync->createPartner(
                "EmailAccounts/" . $accountId,
                $this->user->getId()
            );
        }

        $conditions = array(
            array(
                "blogic" => Where::COMBINED_BY_AND,
                "field" => "email_account",
                "operator" => Where::OPERATOR_EQUAL_TO,
                "condValue" => $accountId,
            ),
            array(
                "blogic" => Where::COMBINED_BY_AND,
                "field" => "mailbox_id",
                "operator" => Where::OPERATOR_EQUAL_TO,
                "condValue" => $mailboxId,
            ),
        );

        $syncColl = $syncPartner->getEntityCollection("email_message", $conditions);

        // Create collection if it does not yet exist
        if (!$syncColl)
        {
            $this->log->info("ReceiverService->syncMailbox: Creating a new collection for $mailboxId");

            $syncColl = $this->collectionFactory->createCollection(EntitySync::COLL_TYPE_ENTITY);
            $syncColl->setObjType("email_message");
            $syncColl->setConditions($conditions);
            $syncPartner->addCollection($syncColl);
            $this->entitySync->savePartner($syncPartner);
        }

        return $syncColl;
    }

    /**
     * Get a mail connection from an email account
     *
     * @param EmailAccountEntity $emailAccount
     * @return AbstractStorage
     * @throws \RuntimeException if an unsupported email account is found
     */
    private function getMailConnection(EmailAccountEntity $emailAccount)
    {
        switch ($emailAccount->getValue("type")) {
            case 'imap':
                return new Imap(array(
                    'host'     => $emailAccount->getValue("host"),
                    'user'     => $emailAccount->getValue("username"),
                    'password' => $emailAccount->getValue("password")
                ));
                break;
            case 'pop3':
                return new Pop3(array(
                    'host'     => $emailAccount->getValue("host"),
                    'user'     => $emailAccount->getValue("username"),
                    'password' => $emailAccount->getValue("password")
                ));
                break;
            default:
                throw new \RuntimeException("Mail account not supported: " . $emailAccount->getValue("type"));
        }
    }

    private function importMessage()
    {
        // 1. Import text into Mail\Message
        // 2. Import Mail\Message into EmailMessageEntity
        // 3. Save the entity to get an ID
        // 4. Upload the original message text and attach to the entity
        // 5. Record an activity if settings permit
    }
}
