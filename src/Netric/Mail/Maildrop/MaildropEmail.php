<?php

declare(strict_types=1);

namespace Netric\Mail\Maildrop;

use Netric\Entity\ObjType\EmailAccountEntity;
use Netric\Entity\ObjType\EmailMessageEntity;
use Netric\Entity\ObjType\UserEntity;
use Netric\Entity\EntityLoader;
use Netric\EntityGroupings\GroupingLoader;
use Netric\EntityGroupings\Group;
use Netric\EntityDefinition\ObjectTypes;
use Netric\FileSystem\FileSystem;
use PhpMimeMailParser\Attachment as MailParserAttachment;
use PhpMimeMailParser\Parser as MailParser;

/**
 * Deliver an email message into an email entity.
 *
 * This is the default and most common maildrop used for incoming
 * messages - or at least it will be when we put email back into netric.
 */
class MaildropEmail implements MaildropInterface
{
    /**
     * Current parser revision
     *
     * This can be used to go back and re-process messages if needed
     */
    const PARSE_REV = 17;

    /**
     * Entity groupings loader
     */
    private GroupingLoader $groupingLoader;

    /**
     * Entity loader
     */
    private EntityLoader $entityLoader;

    /**
     * Filesystem for saving attachments
     */
    private FileSystem $fileSystem;

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
        EntityLoader $entityLoader,
        GroupingLoader $groupingLoader,
        FileSystem $fileSystem,
    ) {
        $this->entityLoader = $entityLoader;
        $this->groupingLoader = $groupingLoader;
        $this->fileSystem = $fileSystem;
    }

    /**
     * The type of entity this maildrop creates
     *
     * @return string one of self::TYPE_
     */
    public function getEntityType(): string
    {
        return MaildropInterface::TYPE_EMAIL;
    }

    /**
     * Process an email message into an entity
     *
     * @param string $messgaeFilePath a local message file, this will be deleted after processing
     * @param EmailAccountEntity $emailAccount The account we are delivering the message to
     * @return string UUID of the created enitty
     */
    public function createEntityFromMessage(string $messageFilePath, EmailAccountEntity $emailAccount): string
    {
        // Get the user to deliver for - this is the onwer of the EmailAccount
        $user = $this->entityLoader->getEntityById($emailAccount->getOwnerId(), $emailAccount->getAccountId());

        // Get Inbox for user, this is just an entity group with the name "Inbox"
        $inboxGroup = $this->getOrCreateInbox($emailAccount);
        $mailboxId = $inboxGroup->getGroupId();

        // Ready to deliver the message, create a parser and point it to the email message file
        $parser = new MailParser();
        $parser->setPath($messageFilePath);

        // Create EmailMessageEntity and import Mail\Message
        $emailEntity = $this->entityLoader->create("email_message", $emailAccount->getAccountId());
        $plainbody = $parser->getMessageBody('text');
        $htmlbody = $parser->getMessageBody('html');

        // Create a unique ID from hashing the file
        $uniqueId = hash_file('md5', $messageFilePath);

        // Check if the message was flagged as spam by the spam filters
        $spamFlag = (trim(strtolower($parser->getHeader('x-spam-flag'))) == "yes") ? true : false;

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
     * @param MailParserAttachment $parserAttach The attachment to import
     * @param EmailMessageEntity $email The email we are adding attachments to
     * @return bool true on success, false on failure
     */
    private function importMailParseAtt(
        MailParserAttachment &$parserAttach,
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
     * Create an Inbox group
     *
     * @param EmailAccountEntity $emailAccount
     * @return Group
     */
    private function getOrCreateInbox(EmailAccountEntity $emailAccount): Group
    {
        // Get user entity from email account
        $user = $this->entityLoader->getEntityById($emailAccount->getOwnerId(), $emailAccount->getAccountId());

        // Get the user's mailbox groupings
        $groupings = $this->groupingLoader->get(
            ObjectTypes::EMAIL_MESSAGE . "/mailbox_id/" . $user->getEntityId(),
            $emailAccount->getAccountId()
        );

        // Add inbox if it does not exist
        if (!$groupings->getByPath("Inbox")) {
            $inbox = new Group();
            $inbox->name = "Inbox";
            $inbox->isSystem = true;
            $inbox->user_id = $emailAccount->getOwnerId();
            $groupings->add($inbox);
            $this->groupingLoader->save($groupings);
        }

        return $groupings->getByPath("Inbox");
    }
}
