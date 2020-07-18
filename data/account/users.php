<?php

/**
 * Default users that should exist in every account
 */

namespace data\account;

use Netric\Entity\ObjType\UserEntity;

return [
    [
        "name" => UserEntity::USER_ANONYMOUS,
        "full_name" => "Anonymous"
    ],
    [
        "name" => UserEntity::USER_CURRENT,
        "full_name" => "Current User"
    ],
    [
        "name" => UserEntity::USER_SYSTEM,
        "full_name" => "System"
    ],
    [
        "name" => UserEntity::USER_WORKFLOW,
        "full_name" => "Workflow"
    ],
];
