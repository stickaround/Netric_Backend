<?php

namespace data\entity_definitions;

use Netric\EntityDefinition\Field;

return [
    'fields' => [
        'action_id' => [
            'title' => 'Action Id',
            'type' => Field::TYPE_INTEGER,
            'subtype' => '',
            'readonly' => false,
        ],
        'ts_execute' => [
            'title' => 'Execute',
            'type' => Field::TYPE_TIMESTAMP,
            'subtype' => '',
            'readonly' => false,
        ],
        'instance_id' => [
            'title' => 'Instance Id',
            'type' => Field::TYPE_INTEGER,
            'subtype' => '',
            'readonly' => false,
        ],
        'inprogress' => [
            'title' => 'In Progress',
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
