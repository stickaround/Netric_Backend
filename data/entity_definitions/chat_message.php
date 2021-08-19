<?php

namespace data\entity_definitions;

use Netric\EntityDefinition\Field;
use Netric\EntityDefinition\ObjectTypes;

return [
    'default_activity_level' => 1,
    'store_revisions' => false,
    'inherit_dacl_ref' => 'chat_room',
    'fields' => [
        'chat_room' => [
            'title' => 'Room',
            'type' => Field::TYPE_OBJECT,
            'subtype' => ObjectTypes::CHAT_ROOM,
            'readonly' => true,
        ],
        'body' => [
            'title' => 'Message',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => false,
        ],
        // List of users this was sent to at the time of creation -
        // which means, every member of a room
        'to' => [
            'title' => 'To',
            'type' => Field::TYPE_OBJECT_MULTI,
            'subtype' => ObjectTypes::USER,
            'readonly' => true
        ],
 		'message_type' => [
            'title' => 'Message Type',
            'type' => Field::TYPE_TEXT,
            'subtype' => '32',
            'optional_values' => [
                // Normal messages 
                "text" => "Text",
                // Special messages like members leaving the chat room
                "notification" => "Notification"
            ],
            'readonly' => true,
            'default' => ["value" => 'text', "on" => "null"]
        ],
    ],
];
