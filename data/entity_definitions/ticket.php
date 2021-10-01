<?php

namespace data\entity_definitions;

use Netric\EntityDefinition\Field;
use Netric\EntityDefinition\ObjectTypes;

return [
    'inherit_dacl_ref' => 'channel_id',
    'fields' => [
        'name' => [
            'title' => 'Subject',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false
        ],
        'description' => [
            'title' => 'Description',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => false
        ],
        'status_id' => [
            'title' => 'Status',
            'type' => Field::TYPE_GROUPING,
            'subtype' => 'object_groupings',
        ],
        'priority_id' => [
            'title' => 'Priority',
            'type' => Field::TYPE_GROUPING,
            'subtype' => 'object_groupings',
        ],
        'status_id' => [
            'title' => 'Status',
            'type' => Field::TYPE_GROUPING,
            'subtype' => 'object_groupings',
        ],
        'status_note' => [
            'title' => 'Status Note',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false
        ],
        'source_id' => [
            'title' => 'Source',
            'type' => Field::TYPE_GROUPING,
            'subtype' => 'object_groupings',
        ],
        'channel_id' => [
            'title' => 'Channel',
            'type' => Field::TYPE_OBJECT,
            'subtype' => ObjectTypes::TICKET_CHANNEL
        ],
        'contact_id' => [
            'title' => 'Contact',
            'type' => Field::TYPE_OBJECT,
            'subtype' => ObjectTypes::CONTACT
        ],
        'is_closed' => [
            'title' => 'Closed',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => false
        ],
        'is_attention_needed' => [
            'title' => 'Attention Needed',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => false
        ],
    ],
];
