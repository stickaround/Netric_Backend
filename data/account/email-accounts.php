<?php

declare(strict_types=1);

namespace data\account;

use Netric\Entity\ObjType\EmailAccountEntity;
use Netric\Mail\Maildrop\MaildropInterface;

return [
    [
        "name" => "Support Email to Tickets",
        "uname" => "general-support-dropbox",
        "type" => EmailAccountEntity::TYPE_DROPBOX,
        "dropbox_create_type" => MaildropInterface::TYPE_TICKET,
    ]
];
