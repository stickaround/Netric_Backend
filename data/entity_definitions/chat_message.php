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
    ],
];
