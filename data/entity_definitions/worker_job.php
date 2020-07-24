<?php

namespace data\entity_definitions;

use Netric\EntityDefinition\Field;

return [
    'is_private' => false,
    'store_revisions' => false,
    'recur_rules' => [
        "field_time_start" => "",
        "field_time_end" => "",
        "field_date_start" => "ts_scheduled",
        "field_date_end" => "ts_scheduled",
        "field_recur_id" => "recur_id",
    ],
    'fields' => [
        'worker_name' => [
            'title' => 'Worker',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => true,
            'require' => true
        ],
        'job_data' => [
            'title' => 'Job Data',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => true,
            'require' => true
        ],
        'ts_scheduled' => [
            'title' => 'When Scheduled',
            'type' => Field::TYPE_TIMESTAMP,
            'subtype' => '',
            'readonly' => true,
            'require' => true
        ],
        'ts_executed' => [
            'title' => 'Time Ran',
            'type' => Field::TYPE_TIMESTAMP,
            'subtype' => '',
            'readonly' => true,
            'require' => true
        ],
        'recur_id' => [
            'title' => 'Recurrance',
            'type' => Field::TYPE_TEXT,
            'subtype' => 36,
            'readonly' => true
        ],
    ],
];
