<?php

declare(strict_types=1);

namespace data\entity_definitions;

use Netric\EntityDefinition\Field;

return array(
    'fields' => array(
        // Textual name
        'name' => array(
            'title' => 'Name',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false,
        ),

        // Longer description of this entity
        'notes' => array(
            'title' => 'Notes',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => false,
        ),

        // Object type we execute against
        'object_type' => array(
            'title' => 'Entity Type',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => true,
        ),

        // Trigger workflow when an entity is created
        'f_on_create' => array(
            'title' => 'On Create',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => false,
        ),

        // Trigger workflow when an entity is updated
        'f_on_update' => array(
            'title' => 'On Update',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => false,
        ),

        // Trigger workflow when an entity is deleted
        'f_on_delete' => array(
            'title' => 'On Delete',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => false,
        ),

        // Check daily if the worklfow should be triggered
        'f_on_daily' => array(
            'title' => 'On Daily',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => false,
        ),

        // Only allow one instance
        'f_singleton' => array(
            'title' => 'Run Only Once',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => false,
        ),

        // Active and ready to be run
        'f_active' => array(
            'title' => 'Active',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => false,
        ),

        // System worfklow - cannot edit name
        'f_system' => array(
            'title' => 'System',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => true,
        ),

        // When the workflow was last executed
        'ts_lastrun' => array(
            'title' => 'Time Last Run',
            'type' => Field::TYPE_TIMESTAMP,
            'subtype' => '',
            'readonly' => true,
        ),
    ),
);
