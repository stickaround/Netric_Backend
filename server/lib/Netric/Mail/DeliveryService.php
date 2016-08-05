<?php
/**
 * @author Sky Stebnicki, sky.stebnicki@aereus.com
 * @copyright Copyright (c) 2016 Aereus Corporation (http://www.aereus.com)
 */
namespace Netric\Mail;

use Netric\Crypt\BlockCipher;
use Netric\Crypt\VaultService;
use Netric\EntityQuery;
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
use Netric\Mail\Headers;
use Netric\EntityLoader;
use Netric\Mail\Storage\Writable\WritableInterface;
use Netric\EntityQuery\Index\IndexInterface;
use Netric\Config\Config;
use Netric\Mime;

/**
 * Service responsible for delivering messages into netric
 *
 * @group integration
 */
class DeliveryService extends AbstractHasErrors
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
     * Index for querying entities
     *
     * @var IndexInterface
     */
    private $entityIndex = null;

    /**
     * Current parser revision
     *
     * This is used to go back and re-process messages if needed
     *
     * @var int
     */
    const PARSE_REV = 16;

    /**
     * Construct the transport service
     *
     * @param Log $log
     * @param EntitySync $entitySync Sync Service
     * @param CollectionFactoryInterface $collectionFactory Factory for constructing collections
     * @param EntityLoader $entityLoader Loader to get and save messages
     * @param GroupingsLoader $groupingsLoader For loading mailbox groupings
     * @param IndexInterface $entityIndex The index for querying entities
     */
    public function __construct(
        Log $log,
        EntitySync $entitySync,
        CollectionFactoryInterface $collectionFactory,
        EntityLoader $entityLoader,
        GroupingsLoader $groupingsLoader,
        IndexInterface $entityIndex
    ) {
        $this->log = $log;
        $this->entitySync = $entitySync;
        $this->collectionFactory = $collectionFactory;
        $this->entityLoader = $entityLoader;
        $this->groupingsLoader = $groupingsLoader;
        $this->entityIndex = $entityIndex;

        /* No longer requried!
        if (!function_exists('mailparse_msg_parse')) {
            throw new \RuntimeException("'pecl/mailparse' is a required extension.");
        }
        */
    }

    /**
     * Import a message from a remote server into a netric entity
     *
     * @param UserEntity $user The user we are delivering on behalf of
     * @param string $uniqueId the id of the message on the server
     * @param Storage\Message $message The message retrieved from the server
     * @param EmailAccountEntity $emailAccount The account we are importing for
     * @param int $mailboxId The mailbox to place the new imssage into
     * @return int The imported message id, 0 on failure, and -1 if already imported
     */
    public function deliverMessage(UserEntity $user, $uniqueId, Storage\Message $message, EmailAccountEntity $emailAccount, $mailboxId)
    {
        $mailMessage = new Message();
        //try  {
            $body = null;

            // Try importing the header and body into a mime message
            if ($message->isMultipart()) {
                $boundary = $message->getHeaderField('content-type', 'boundary');
                $body = Mime\Message::createFromMessage($message->getContent(), $boundary);
            } else {
                $body = $message->getContent();
            }

            $mailMessage->setHeaders($message->getHeaders());
            $mailMessage->setBody($body);
        /*} catch (Header\Exception\InvalidArgumentException $ex) {
            $this->log->error(
                "Failed to convert serve message to mime because " .
                "a bad header was encountered $uniqueId: " . $ex->getMessage()
            );
            return 0;
        } catch (\Exception $ex) {
            $this->log->error("Failed to convert serve message to mime $uniqueId: " . $ex->getMessage());
            return 0;
        }*/

        // Check to make sure this message was not already imported - no duplicates
        $query = new EntityQuery("email_message");
        $query->where("mailbox_id")->equals($mailboxId);
        $query->andWhere("message_uid")->equals($uniqueId);
        $query->andWhere("email_account")->equals($emailAccount->getId());
        $query->andWhere("subject")->equals($mailMessage->getSubject());
        $result = $this->entityIndex->executeQuery($query);
        $num = $result->getNum();
        if ($num > 0) {
            $emailEntity = $result->getEntity(0);
            return $emailEntity;
        }

        // Also checked previously deleted and return -1 if found
        $query = new EntityQuery("email_message");
        $query->where("mailbox_id")->equals($mailboxId);
        $query->andWhere("message_uid")->equals($uniqueId);
        $query->andWhere("email_account")->equals($emailAccount->getId());
        $query->andWhere("subject")->equals($mailMessage->getSubject());
        $query->andWhere("f_deleted")->equals(true);
        $result = $this->entityIndex->executeQuery($query);
        $num = $result->getNum();
        if ($num > 0) {
            return -1;
        }

        // Create EmailMessageEntity and import Mail\Message
        $emailEntity = $this->entityLoader->create("email_message");
        //try  {
            $emailEntity->fromMailMessage($mailMessage);
        /*} catch (Header\Exception\InvalidArgumentException $ex) {
            $this->log->error("Bad header found in $uniqueId: " . $ex->getMessage());
            return 0;
        } catch (\Exception $ex) {
            $this->log->error("Error importing $uniqueId: " . $ex->getMessage());
            return 0;
        }*/

        $emailEntity->setValue("email_account", $emailAccount->getId());
        $emailEntity->setValue("owner_id", $user->getId());
        $emailEntity->setValue("mailbox_id", $mailboxId);
        $emailEntity->setValue("message_uid", $uniqueId);
        $emailEntity->setValue("flag_seen", ($message->hasFlag(Storage::FLAG_UNSEEN)) ? false : true);
        return $this->entityLoader->save($emailEntity);
    }

    private function getMimeFromBody(Storage\Part $mime)
    {
        $mimeMessage = new Mime\Message();

        $foundPart = null;
        $parts = new \RecursiveIteratorIterator($mime);
        foreach ($parts as $part) {

            // Initialize the part to add
            $mimePart = null;

            if ($part->isMultipart()) {
                $subMimeMessage = $this->getMimeFromBody($part);
                $mimePart = new Mime\Part($subMimeMessage->generateMessage());
                $mimePart->setType($part->contentType);
                $mimePart->setBoundary($subMimeMessage->getMime()->boundary());
            } else {
                $mimePart = new Mime\Part($part->getContent());
                $headers = $part->getHeaders();
            }

            $mimeMessage->addPart($mimePart);

            if (strtok($part->contentType, ';') == 'text/plain') {
                $foundPart = $part;
                break;
            }
        }

        return $mimeMessage;
    }
}
