<?php

namespace data\account;

use Netric\Entity\ObjType\UserEntity;

return [
    ["id" => UserEntity::GROUP_USERS, "name" => "Users", "f_admin" => 'f'],
    ["id" => UserEntity::GROUP_EVERYONE, "name" => "Everyone", "f_admin" => 'f'],
    ["id" => UserEntity::GROUP_CREATOROWNER, "name" => "Creator Owner", "f_admin" => 'f'],
    ["id" => UserEntity::GROUP_ADMINISTRATORS, "name" => "Administrators", "f_admin" => 't'],
];
