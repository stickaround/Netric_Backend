<?php
namespace data\entity_definitions;
use Netric\EntityDefinition\Field;

return array(
    'fields' => array(
        // Textual name
        'name' => array(
            'title'=>'Name',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>false,
        ),

        // The type of actions we can execute
        'type_name' => array(
            'title' => 'Type',
            'type'=>Field::TYPE_TEXT,
            'subtype' => '32',
            'readonly' => false,
            'optional_values' => array(
                "approval" => "Request Approval",
                "assign" => "Assign to",
                "check_condition" => "Condition is Met",
                "create_entity" => "Create Entity",
                "send_email" => "Send an Email",
                "start_workflow" => "Start a Workflow",
                "update_field" => "Update a Field",
                "wait_condition" => "Wait",
                "webhook" => "Call Web Page (Webhook)",
            ),
        ),

        // Longer description of this entity
        'notes' => array(
            'title'=>'Notes',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>false,
        ),

        // The workflow we are a child of
        'workflow_id' => array(
            'title'=>'Workflow',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'workflow',
            'readonly'=>true,
        ),

        // Optional parent action
        'parent_action_id' => array(
            'title'=>'Parent Action',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'workflow_action',
            'readonly'=>true,
        ),

        // Action data - json encoded
        'data' => array(
            'title'=>'Data',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>true,
        ),
    ),
);
