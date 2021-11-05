<?php

declare(strict_types=1);

namespace data\account;

// Create default ticket channels
return [
    [
        "name" => "General Support",
        "uname" => "general-support-channel",
        // Use this to link the email dropbox to this support channel
        "lookup_email_account_uname" => "general-support-dropbox",
    ]
];
