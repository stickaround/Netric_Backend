<?php

namespace data\entity_definitions;

use Netric\EntityDefinition\Field;

return [
    'fields' => [
        'comment' => [
            'title' => 'Comment',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => true
        ],
        'notified' => [
            'title' => 'Notified',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => true
        ],
        // Object data comma separated
        'notify' => [
            'title' => 'Sent To',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => true
        ],
        'ts_entered' => [
            'title' => 'Date',
            'type' => Field::TYPE_TIMESTAMP,
            'subtype' => '',
            'readonly' => true,
            'default' => [
                "value" => "now",
                "on" => "create"
            ],
        ],
        'obj_reference' => [
            'title' => 'Reference',
            'type' => Field::TYPE_OBJECT,
            'subtype' => '',
            'readonly' => true
        ],
        'sent_by' => [
            'title' => 'Sent By',
            'type' => Field::TYPE_OBJECT,
            'subtype' => '',
            'readonly' => true
        ],
    ],
    'inherit_dacl_ref' => 'obj_reference',
];
