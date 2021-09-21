<?php

declare(strict_types=1);

namespace data\entity_definitions;

use Netric\EntityDefinition\Field;

return [
    'fields' => [
        // Textual name
        'name' => [
            'title' => 'Name',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false,
        ],

        // Longer description of this entity
        'notes' => [
            'title' => 'Notes',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => false,
        ],

        // Object type we execute against
        'object_type' => [
            'title' => 'Entity Type',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => true,
        ],

        // Trigger workflow when an entity is created
        'f_on_create' => [
            'title' => 'On Create',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => false,
        ],

        // Trigger workflow when an entity is updated
        'f_on_update' => [
            'title' => 'On Update',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => false,
        ],

        // Trigger workflow when an entity is deleted
        'f_on_delete' => [
            'title' => 'On Delete',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => false,
        ],

        // Check daily if the worklfow should be triggered
        'f_on_daily' => [
            'title' => 'On Daily',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => false,
        ],

        // Only allow one instance
        'f_singleton' => [
            'title' => 'Run Only Once',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => false,
        ],

        // Active and ready to be run
        'f_active' => [
            'title' => 'Active',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => false,
        ],

        // System worfklow - cannot edit name
        'f_system' => [
            'title' => 'System',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => true,
        ],

        // When the workflow was last executed
        'ts_lastrun' => [
            'title' => 'Time Last Run',
            'type' => Field::TYPE_TIMESTAMP,
            'subtype' => '',
            'readonly' => true,
        ],
    ],
];
