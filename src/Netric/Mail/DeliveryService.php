<?php

namespace Netric\Mail;

use Netric\Account\Account;
use Netric\EntityGroupings\Group;
use Netric\EntityQuery\EntityQuery;
use Netric\Error\AbstractHasErrors;
use Netric\Entity\ObjType\EmailAccountEntity;
use Netric\Entity\ObjType\UserEntity;
use Netric\FileSystem\FileSystem;
use Netric\Log\Log;
use Netric\EntityGroupings\GroupingLoader;
use Netric\Entity\ObjType\EmailMessageEntity;
use Netric\Mail\Exception\AddressNotFoundException;
use Netric\Entity\EntityLoader;
use Netric\EntityQuery\Index\IndexInterface;
use Netric\EntityDefinition\ObjectTypes;
use PhpMimeMailParser;

/**
 * Service responsible for delivering messages into netric
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
     * Entity groupings loader
     *
     * @var GroupingLoader
     */
    private $groupingLoader = null;

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
     * Filesystem for saving attachments
     *
     * @var FileSystem
     */
    private $fileSystem = null;

    /**
     * Construct the transport service
     *
     * @param Log $log
     * @param EntityLoader $entityLoader Loader to get and save messages
     * @param GroupingLoader $groupingLoader For loading mailbox groupings
     * @param IndexInterface $entityIndex The index for querying entities,
     * @param FileSystem $fileSystem For saving attachments
     */
    public function __construct(
        Log $log,
        EntityLoader $entityLoader,
        GroupingLoader $groupingLoader,
        IndexInterface $entityIndex,
        FileSystem $fileSystem
    ) {
        $this->log = $log;
        $this->entityLoader = $entityLoader;
        $this->groupingLoader = $groupingLoader;
        $this->entityIndex = $entityIndex;
        $this->fileSystem = $fileSystem;

        if (!function_exists('mailparse_msg_parse')) {
            throw new \RuntimeException("'pecl/mailparse' is a required extension.");
        }
    }

    /**
     * Import a message from a remote server into a netric entity
     *
     * @param string $emailAddress The address to deliver to
     * @param string $filePath Path to the file containing the message to import
     * @return int The imported message id, 0 on failure, and -1 if already imported
     */
    public function deliverMessageFromFile(
        string $emailAddress,
        string $filePath,
        Account $account
    ): string {

        // TODO: Check if the email is a drop-box email and process accordingly
        // --------------------------------------------------------------------

        // First get the email account from the address
        $emailAccount = $this->getEmailAccountFromAddress($emailAddress, $account->getAccountId());
        if (!$emailAccount) {
            throw new AddressNotFoundException("$emailAddress not found");
        }

        // Get user entity from email account
        $user = $this->entityLoader->getEntityById(
            $emailAccount->getOwnerId(),
            $account->getAccountId()
        );

        // Get Inbox for user
        $mailboxGroups = $this->groupingLoader->get(
            ObjectTypes::EMAIL_MESSAGE . "/mailbox_id/" . $user->getEntityId(),
            $user->getAccountId()
        );
        $inboxGroup = $mailboxGroups->getByPath('Inbox');
        if (!$inboxGroup) {
            // User exists, we need to create the inbox
            $inboxGroup = $this->createInbox($emailAccount);
        }
        $mailboxId = $inboxGroup->getGroupId();

        // Ready to deliver the message, create a parser and point it to the email message file
        $parser = new PhpMimeMailParser\Parser();
        $parser->setPath($filePath);

        // Create EmailMessageEntity and import Mail\Message
        $emailEntity = $this->entityLoader->create("email_message", $account->getAccountId());
        $plainbody = $parser->getMessageBody('text');
        $htmlbody = $parser->getMessageBody('html');

        // Create a unique ID from hashing the file
        $uniqueId = hash_file('md5', $filePath);

        // Check if the message was flagged as spam by the spam filters
        $spamFlag = (trim(strtolower($parser->getHeader('x-spam-flag'))) == "yes") ? true : false;

        // Make sure messages are unicode
        /*
        $htmlCharType = $this->getCharTypeFromHeaders($parser->getMessageBodyHeaders("html"));
        $plainCharType = $this->getCharTypeFromHeaders($parser->getMessageBodyHeaders("text"));
        ini_set('mbstring.substitute_character', "none");
        $plainbody= mb_convert_encoding($plainbody, 'UTF-8', $plainCharType);
        $htmlbody= mb_convert_encoding($htmlbody, 'UTF-8', $htmlCharType);
        */

        $origDate = $parser->getHeader('date');
        if (is_array($origDate)) {
            $origDate = $origDate[count($origDate) - 1];
        }
        if (!strtotime($origDate) && $origDate) {
            $origDate = substr($origDate, 0, strrpos($origDate, " "));
        }
        $messageDate = ($origDate) ? date(DATE_RFC822, strtotime($origDate)) : date(DATE_RFC822);

        // Create new mail object and save it to ANT
        $emailEntity->setValue("message_date", $messageDate);
        $emailEntity->setValue("parse_rev", self::PARSE_REV);
        $emailEntity->setValue("subject", $parser->getHeader('subject'));
        $emailEntity->setValue("sent_from", $parser->getHeader('from'));
        $emailEntity->setValue("send_to", $parser->getHeader('to'));
        $emailEntity->setValue("cc", $parser->getHeader('cc'));
        $emailEntity->setValue("bcc", $parser->getHeader('bcc'));
        $emailEntity->setValue("in_reply_to", $parser->getHeader('in-reply-to'));
        $emailEntity->setValue("flag_spam", $spamFlag);
        $emailEntity->setValue("message_id", $parser->getHeader('message-id'));
        if ($htmlbody) {
            $emailEntity->setValue("body", $htmlbody);
            $emailEntity->setValue("body_type", "html");
        } elseif ($plainbody) {
            $emailEntity->setValue("body", $plainbody);
            $emailEntity->setValue("body_type", "plain");
        }

        $attachments = $parser->getAttachments();
        foreach ($attachments as $att) {
            $this->importMailParseAtt($att, $emailEntity, $user);
        }

        // Cleanup resources
        $parser = null;
        $emailEntity->setValue("email_account", $emailAccount->getEntityId());
        $emailEntity->setValue("owner_id", $user->getEntityId());
        $emailEntity->setValue("mailbox_id", $mailboxId);
        $emailEntity->setValue("message_uid", $uniqueId);
        $emailEntity->setValue("flag_seen", false);
        $this->entityLoader->save($emailEntity, $user);
        return $emailEntity->getEntityId();
    }

    /**
     * Process attachments for a message being parsed by mimeparse
     *
     * @param PhpMimeMailParser\Attachment $parserAttach The attachment to import
     * @param EmailMessageEntity $email The email we are adding attachments to
     * @return bool true on success, false on failure
     */
    private function importMailParseAtt(
        PhpMimeMailParser\Attachment &$parserAttach,
        EmailMessageEntity &$email,
        UserEntity $user
    ) {
        /*
         * Write attachment to temp file
         *
         * It is important to use streams here to try and keep the attachment out of
         * memory if possible. The parser should already have decoded the bodies for
         * us so no need to use base64_decode or any other decoding.
         */
        $tmpFile = tmpfile();
        $buf = null;
        while (($buf = $parserAttach->read()) != false) {
            fwrite($tmpFile, $buf);
        }

        // Rewind stream
        fseek($tmpFile, 0);

        // Stream the temp file into the fileSystem
        $file = $this->fileSystem->createFile("%tmp%", $parserAttach->getFilename(), $user, true);
        $result = $this->fileSystem->writeFile($file, $tmpFile, $user);
        $email->addMultiValue("attachments", $file->getEntityId(), $file->getName());
    }

    /**
     * Get an email account from an address if it exists
     *
     * @param string $emailAddress
     * @param string $accountId
     * @return EmailAccountEntity|null
     */
    private function getEmailAccountFromAddress(string $emailAddress, string $accountId): ?EmailAccountEntity
    {
        // Query email accounts for unique email address
        $query = new EntityQuery(ObjectTypes::EMAIL_ACCOUNT, $accountId);
        $query->where("address")->equals($emailAddress);
        $result = $this->entityIndex->executeQuery($query);
        $num = $result->getNum();
        if (!$num) {
            return null;
        }

        return $result->getEntity(0);
    }

    /**
     * Create an Inbox group
     *
     * @param EmailAccountEntity $emailAccount
     * @return Group
     */
    private function createInbox(EmailAccountEntity $emailAccount): Group
    {
        // Get user entity from email account
        $user = $this->entityLoader->getEntityById($emailAccount->getOwnerId(), $emailAccount->getAccountId());

        $groupings = $this->groupingLoader->get(ObjectTypes::EMAIL_MESSAGE . "/mailbox_id/" . $user->getEntityId());

        $inbox = new Group();
        $inbox->name = "Inbox";
        $inbox->isSystem = true;
        $inbox->user_id = $emailAccount->getOwnerId();
        $groupings->add($inbox);
        $this->groupingLoader->save($groupings);
        return $groupings->getByPath("Inbox");
    }
}
