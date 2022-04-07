<?php

declare(strict_types=1);

namespace Netric\Mail\Maildrop;

use Netric\Entity\ObjType\EmailAccountEntity;
use Netric\EntityDefinition\ObjectTypes;
use Netric\Entity\EntityLoader;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityQuery\Index\IndexInterface;
use Netric\FileSystem\FileSystem;
use PhpMimeMailParser\Parser as MailParser;

/**
 * Deliver an email message into a comment entity
 *
 * This happens when someone replies to a comment straight from email,
 * the 'reply-to' will be something like comment.[entity_id]@[account].netric.com
 * and should be delivered straight as a comment.
 *
 * The magic here is determing which email to attribute the comment to.
 */
class MaildropComment extends AbstractMaildrop implements MaildropInterface
{
    /**
     * Entity loader
     */
    private EntityLoader $entityLoader;

    /**
     * Filesystem for saving attachments
     */
    private FileSystem $fileSystem;

    /**
     * Index used to search for entities
     *
     * @var IndexInterface
     */
    private IndexInterface $entityIndex;

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
        FileSystem $fileSystem,
        IndexInterface $entityIndex
    ) {
        $this->entityLoader = $entityLoader;
        $this->fileSystem = $fileSystem;
        $this->entityIndex = $entityIndex;
    }

    /**
     * The type of entity this maildrop creates
     *
     * @return string one of self::TYPE_
     */
    public function getEntityType(): string
    {
        return MaildropInterface::TYPE_COMMENT;
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
        $user = $this->entityLoader->getByUniqueName(
            ObjectTypes::USER,
            UserEntity::USER_SYSTEM,
            $emailAccount->getAccountId()
        );
        //$user = $this->entityLoader->getEntityById($emailAccount->getOwnerId(), $emailAccount->getAccountId());

        // Ready to deliver the message, create a parser and point it to the email message file
        $parser = new MailParser();
        $parser->setPath($messageFilePath);

        // First check if the message was flagged as spam by the spam filters
        // TODO: We shhould probably not deliver this?
        // $spamFlagText = $parser->getHeader('x-spam-flag') ? $parser->getHeader('x-spam-flag') : '';
        // $spamFlag = trim(strtolower($spamFlagText)) === "yes";

        // Get comment bodies
        $plainbody = $parser->getMessageBody('text');
        $htmlbody = $parser->getMessageBody('html');

        if ($htmlbody && !$plainbody) {
            $plainbody = $this->htmlBodyToPlainText($htmlbody);
        }

        // Create new comment and set the properties
        $comment = $this->entityLoader->create(ObjectTypes::COMMENT, $emailAccount->getAccountId());
        $comment->setValue("comment", $plainbody);
        $comment->setValue("obj_reference", $emailAccount->getValue('dropbox_obj_reference'));
        // We might use this later to detect replying to another
        //$comment->setValue("in_reply_to", $parser->getHeader('in-reply-to'));
        // $comment->setValue("from", $parser->getHeader('from'));
        // $comment->setValue("email_account", $emailAccount->getEntityId());
        // $comment->setValue("owner_id", $user->getEntityId());
        // $comment->setValue("mailbox_id", $mailboxId);
        // $comment->setValue("message_uid", $uniqueId);
        // $comment->setValue("flag_seen", false);

        $attachments = $parser->getAttachments();
        foreach ($attachments as $att) {
            $this->importAttachments($att, $comment, $user, $this->fileSystem);
        }

        $this->entityLoader->save($comment, $user);

        // Cleanup resources
        $parser = null;

        return $comment->getEntityId();
    }

    /**
     * Get the user id or contact id of the sender
     */
    private function getSenderId($emailAddress): string
    {
        // Not found
        return '';
    }
}
