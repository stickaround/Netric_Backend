<?php

namespace data\entity_definitions;

use Netric\EntityDefinition\Field;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\ObjectTypes;

return [
    'parent_field' => "parent",
    'child_dacls' => ["case", "task", "project_milestone"],
    'fields' => [
        'name' => [
            'title' => 'Title',
            'type' => Field::TYPE_TEXT,
            'subtype' => '128',
            'readonly' => false
        ],
        'notes' => [
            'title' => 'Description',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => false
        ],
        'news' => [
            'title' => 'Updates',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => false
        ],
        'date_started' => [
            'title' => 'Start Date',
            'type' => Field::TYPE_DATE,
            'subtype' => '',
            'readonly' => false,
            'default' => ["value" => "now", "on" => "create"]
        ],
        'date_deadline' => [
            'title' => 'Deadline',
            'type' => Field::TYPE_DATE,
            'subtype' => '',
            'readonly' => false
        ],
        'date_completed' => [
            'title' => 'Completed',
            'type' => Field::TYPE_DATE,
            'subtype' => '',
            'readonly' => false
        ],
        'parent' => [
            'title' => 'Parent',
            'type' => Field::TYPE_OBJECT,
            'subtype' => 'project',
            'fkey_table' => ["key" => "id", "title" => "name"]
        ],
        'priority' => [
            'title' => 'Priority',
            'type' => Field::TYPE_GROUPING,
            'subtype' => 'object_groupings',
        ],
        'customer_id' => [
            'title' => 'Contact',
            'type' => Field::TYPE_OBJECT,
            'subtype' => ObjectTypes::CONTACT
        ],
        'groups' => [
            'title' => 'Groups',
            'type' => Field::TYPE_GROUPING_MULTI,
            'subtype' => 'object_groupings',
        ],
        'members' => [
            'title' => 'Members',
            'type' => Field::TYPE_OBJECT_MULTI,
            'subtype' => ObjectTypes::USER,
            'default' => ["value" => UserEntity::USER_CURRENT, "on" => "create"]
        ],
        'image_id' => [
            'title' => 'Image',
            'type' => Field::TYPE_OBJECT,
            'subtype' => ObjectTypes::FILE
        ],
    ],
];
