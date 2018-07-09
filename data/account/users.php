<?php
/**
 * Default users that should exist in every account
 */
namespace data\account;

use Netric\Entity\ObjType\UserEntity;

return array(
    array(
        "guid"=>UserEntity::USER_ANONYMOUS,
        "name"=>"anonymous",
        "full_name"=>"Anonymous"
    ),
    array(
        "guid"=>UserEntity::USER_CURRENT,
        "name"=>"current.user",
        "full_name"=>"Current User"
    ),
    array(
        "guid"=>UserEntity::USER_SYSTEM,
        "name"=>"system",
        "full_name"=>"System"
    ),
    array(
        "guid"=>UserEntity::USER_WORKFLOW,
        "name"=>"workflow",
        "full_name"=>"Workflow"
    ),
);
