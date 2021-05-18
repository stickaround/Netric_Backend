<?php

namespace data\entity_definitions;

use Netric\EntityDefinition\Field;
use Netric\EntityDefinition\ObjectTypes;
use Netric\Entity\ObjType\UserEntity;

return [
    'default_activity_level' => 1,
    'store_revisions' => false,
    'fields' => [
        'subject' => [
            'title' => 'Subject',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => true,
        ],
        'scope' => [
            'title' => 'Scope',
            'type' => Field::TYPE_TEXT,
            'subtype' => '32',
            'optional_values' => [
                // Channels are used for persistent rooms
                "channel" => "Channel",
                // Direct mesage/threads between two or more people
                "direct" => "Direct"
            ],
            'readonly' => true,
            'default' => ["value" => 'channel', "on" => "null"]
        ],
        'last_message_body' => [
            'title' => 'Last Message',
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
    ],
];
