<?php

namespace data\entity_definitions;

use Netric\EntityDefinition\Field;

return [
    'fields' => [
        'entity_definition_id' => [
            'title' => 'Entity Type ID',
            'type' => Field::TYPE_UUID,
            'subtype' => '',
            'readonly' => false,
            'required' => true
        ],
        'object_type' => [
            'title' => 'Object Type',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => false,
        ],
        'act_on_entity_id' => [
            'title' => 'Entity',
            'type' => Field::TYPE_UUID,
            'subtype' => '',
            'readonly' => false,
            'required' => true
        ],
        'workflow_id' => [
            'title' => 'Workflow',
            'type' => Field::TYPE_UUID,
            'subtype' => '',
            'readonly' => false
        ],
        'ts_started' => [
            'title' => 'Entered By',
            'type' => Field::TYPE_TIMESTAMP,
            'subtype' => '',
            'readonly' => false,
            'default' => [
                "value" => 'now',
                "on" => "null",
            ],
        ],
        'ts_completed' => [
            'title' => 'Completed',
            'type' => Field::TYPE_TIMESTAMP,
            'subtype' => '',
            'readonly' => false
        ],
        'f_completed' => [
            'title' => 'All Actions Run',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => false,
            'default' => [
                "value" => 'f',
                "on" => "null",
            ],
        ]
    ]
];
