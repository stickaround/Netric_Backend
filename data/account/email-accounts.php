<?php

declare(strict_types=1);

namespace data\account;

use Netric\Entity\ObjType\EmailAccountEntity;
use Netric\Mail\Maildrop\MaildropInterface;

return [
    [
        "name" => "Support",
        "uname" => "general-support-dropbox",
        "address_user" => "support", // @[accountname].netric.com will be added
        "type" => EmailAccountEntity::TYPE_DROPBOX,
        "dropbox_create_type" => MaildropInterface::TYPE_TICKET,
    ]
];
