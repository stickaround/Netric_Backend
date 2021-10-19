<?php

namespace data\entity_definitions;

use Netric\EntityDefinition\Field;
use Netric\EntityDefinition\ObjectTypes;

return [
    'fields' => [
        'obj_reference' => [
            'title' => 'Reference',
            'type' => Field::TYPE_OBJECT,
            'subtype' => '',
            'system' => true,
            'readonly' => true
        ],
        'member_id' => [
            'title' => 'User',
            'type' => Field::TYPE_OBJECT,
            'subtype' => ObjectTypes::USER,
            'readonly' => true,
            'system' => true,
        ],
        'reaction' => [
            'title' => 'Reaction',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => false,
            'system' => true,
        ]
    ],
];
