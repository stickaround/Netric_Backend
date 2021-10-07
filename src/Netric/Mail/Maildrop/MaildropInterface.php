<?php

declare(strict_types=1);

namespace Netric\Mail\Maildrop;

use Netric\Entity\ObjType\EmailAccountEntity;
use Netric\EntityDefinition\ObjectTypes;

/**
 * A Maildrop is a driver that takes a local file and converts
 * it into a netric entity - usually an Email and/or EmailThread
 * but it can also be a comment or ticket or note or any other
 * dropbox.
 */
interface MaildropInterface
{
    /**
     * Define the supported types
     */
    const TYPE_EMAIL = ObjectTypes::EMAIL_MESSAGE;
    const TYPE_COMMENT = ObjectTypes::COMMENT;
    const TYPE_TICKET = ObjectTypes::TICKET;

    /**
     * The type of entity this maildrop creates
     *
     * @return string one of self::TYPE_
     */
    public function getEntityType(): string;

    /**
     * Process an email message into an entity
     *
     * @param string $messgaeFilePath a local message file, this will be deleted after processing
     * @param EmailAccountEntity $emailAccount The account we are delivering the message to
     * @return string UUID of the created enitty
     */
    public function createEntityFromMessage(string $messageFilePath, EmailAccountEntity $emailAccount): string;
}
