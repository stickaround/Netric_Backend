<?php

namespace data\entity_definitions;

use Netric\EntityDefinition\Field;
use Netric\Entity\ObjType\UserEntity;

return [
    'fields' => [
        "name" => [
            'title' => 'Title',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false,
            "required" => true,
        ],

        "notes" => [
            'title' => 'Details',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => false,
        ],

        "user_name" => [
            'title' => 'User Name',
            'type' => Field::TYPE_TEXT,
            'subtype' => '128',
            'readonly' => true,
        ],

        "f_readonly" => [
            'title' => 'Read Only',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => true,
        ],

        "is_private" => [
            'title' => 'Private',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => true,
            'default' => [
                "value" => 'f',
                "on" => "null",
            ],
        ],

        "direction" => [
            'title' => 'Direction',
            'type' => Field::TYPE_TEXT,
            'subtype' => '1',
            'readonly' => false,
            'optional_values' => [
                "n" => "None",
                "i" => "Incoming",
                "o" => "Outgoing",
            ],
        ],

        "level" => [
            'title' => 'Level',
            'type' => Field::TYPE_NUMBER,
            'subtype' => 'integer',
            'readonly' => true,
            'default' => [
                "value" => "3",
                "on" => "null",
            ],
        ],

        // What action was done
        "verb" => [
            'title' => 'Action',
            'type' => 'text',
            'subtype' => '32',
            'readonly' => true,
            'default' => [
                "value" => 'create',
                "on" => "null",
            ],
        ],

        // Optional reference to object that was used to perform the action/verb
        'verb_object' => [
            'title' => 'Origin',
            'type' => Field::TYPE_OBJECT,
            'subtype' => '',
            'readonly' => true,
        ],

        // Who/what did the action
        'subject' => [
            'title' => 'Subject',
            'type' => Field::TYPE_OBJECT,
            'subtype' => '',
            'readonly' => true,
        ],

        // What the action was done to
        'obj_reference' => [
            'title' => 'Reference',
            'type' => Field::TYPE_OBJECT,
            'subtype' => '',
            'readonly' => true,
        ],

        // File attachments
        'attachments' => [
            'title' => 'Attachments',
            'type' => Field::TYPE_OBJECT_MULTI,
            'subtype' => 'file',
        ],

        'user_id' => [
            'title' => 'User',
            'type' => Field::TYPE_OBJECT,
            'subtype' => 'user',
            'default' => [
                "value" => UserEntity::USER_CURRENT,
                "on" => "null"
            ],
        ],

        'type_id' => [
            'title' => 'Type',
            'type' => Field::TYPE_GROUPING,
            'subtype' => 'object_groupings',
            "required" => true
        ],
    ],
    'store_revisions' => false,
];
