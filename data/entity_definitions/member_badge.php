<?php

namespace data\entity_definitions;

use Netric\EntityDefinition\Field;
use Netric\EntityDefinition\ObjectTypes;

return [
    'default_activity_level' => 5,
    'store_revisions' => false,
    'fields' => [
        'obj_reference' => [
            'title' => 'References',
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
        'num_unseen' => [
            'title' => 'Num unseen',
            'type' => Field::TYPE_INTEGER,
            'subtype' => '',
            'readonly' => true,
            'system' => true,
        ],
        'is_seen' => [
            'title' => 'Seen',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => false,
            'system' => true,
        ],
    ],
];
