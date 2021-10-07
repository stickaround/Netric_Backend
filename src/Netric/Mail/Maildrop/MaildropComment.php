<?php

declare(strict_types=1);

namespace Netric\Mail\Maildrop;

use Netric\Entity\ObjType\EmailAccountEntity;

/**
 * Deliver an email message into a comment entity
 *
 * This happens when someone replies to a comment straight from email,
 * the 'reply-to' will be something like comment.[entity_id]@[account].netric.com
 * and should be delivered straight as a comment.
 *
 * The magic here is determing which email to attribute the comment to.
 */
class MaildropComment implements MaildropInterface
{

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
        return '';
    }
}
