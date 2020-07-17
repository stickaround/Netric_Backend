<?php

/**
 * Default users that should exist in every account
 */

namespace data\account;

use Netric\Entity\ObjType\UserEntity;

return [
    [
        "name" => "anonymous",
        "full_name" => "Anonymous"
    ],
    [
        "name" => "current.user",
        "full_name" => "Current User"
    ],
    [
        "name" => "system",
        "full_name" => "System"
    ],
    [
        "name" => "workflow",
        "full_name" => "Workflow"
    ],
];
