<?php

namespace data\entity_definitions;

use Netric\EntityDefinition\Field;

return [
    'default_activity_level' => 5,
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
        'notify' => [
            'title' => 'Send To',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => true
        ],
        'obj_reference' => [
            'title' => 'Concerning',
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
        'link_title' => [
            'title' => 'Link Title',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => false,
        ],
        'link_image_url' => [
            'title' => 'Link Image',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => false,
        ],
        'link_description' => [
            'title' => 'Link Description',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => false,
        ]
    ],
];
