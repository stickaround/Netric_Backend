<?php

namespace data\account;

use Netric\Entity\ObjType\UserEntity;

return [
    ['name' => UserEntity::GROUP_USERS, 'f_admin' => 'f'],
    ['name' => UserEntity::GROUP_EVERYONE, 'f_admin' => 'f'],
    ['name' => UserEntity::GROUP_CREATOROWNER, 'f_admin' => 'f'],
    ['name' => UserEntity::GROUP_ADMINISTRATORS, 'f_admin' => 't'],
];
