<?php

namespace data\entity_definitions;

use Netric\EntityDefinition\Field;
use Netric\EntityDefinition\ObjectTypes;
use Netric\Entity\ObjType\UserEntity;

return [
    'default_activity_level' => 1,
    'store_revisions' => false,
    'fields' => [
        'name' => [
            'title' => 'Name',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => true,
        ],
        'members' => [
            'title' => 'People',
            'type' => Field::TYPE_OBJECT_MULTI,
            'subtype' => ObjectTypes::USER,
            'readonly' => true,
            'default' => ["value" => UserEntity::USER_CURRENT, "on" => "null"]
        ],
        'image_id' => [
            'title' => 'Image',
            'type' => Field::TYPE_OBJECT,
            'subtype' => ObjectTypes::FILE
        ],
        // Optional email account that can be used to add to this channel
        'email_account_id' => [
            'title' => 'Email Account',
            'type' => Field::TYPE_OBJECT,
            'subtype' => ObjectTypes::EMAIL_ACCOUNT,
        ],
    ],
];
