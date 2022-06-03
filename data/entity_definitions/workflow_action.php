<?php

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
            'required' => true,
        ],

        // The type of actions we can execute
        'type_name' => [
            'title' => 'Type',
            'type' => Field::TYPE_TEXT,
            'subtype' => '32',
            'readonly' => false,
            'required' => true,
            'optional_values' => [
                "approval" => "Request Approval",
                "assign" => "Assign to",
                "check_condition" => "Condition is Met",
                "create_entity" => "Create Entity",
                "send_email" => "Send an Email",
                "start_workflow" => "Start a Workflow",
                "update_field" => "Update a Field",
                "wait_condition" => "Wait",
                "webhook" => "Call Web Page (Webhook)",
            ],
        ],

        // System worfklow - cannot edit name
        'f_system' => [
            'title' => 'System',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => true,
        ],

        // Longer description of this entity
        'notes' => [
            'title' => 'Notes',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => false,
        ],

        // The workflow we are a child of
        'workflow_id' => [
            'title' => 'Workflow',
            'type' => Field::TYPE_OBJECT,
            'subtype' => 'workflow',
            'readonly' => true,
        ],

        // Optional parent action
        'parent_action_id' => [
            'title' => 'Parent Action',
            'type' => Field::TYPE_OBJECT,
            'subtype' => 'workflow_action',
            'readonly' => true,
        ],

        // Action data - json encoded
        'data' => [
            'title' => 'Action Data',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => true,
        ],
    ],
];
