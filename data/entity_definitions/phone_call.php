<?php
namespace data\entity_definitions;
use Netric\EntityDefinition\Field;

return array(
    'default_activity_level' => 5,
    'fields' => array(
        // Textual name
        'name' => array(
            'title'=>'Subject',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'512',
            'readonly'=>false,
        ),

        "direction" => array(
            'title'=>'Direction',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'1',
            'readonly'=>false,
            'optional_values'=>array(
                "i"=>"Inbound",
                "o"=>"Outbound"
            ),
        ),

        // Textual name
        'result' => array(
            'title'=>'Outcome',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'512',
            'readonly'=>false,
        ),

        "ts_start" => array(
            'title'=>'Call Start Time',
            'type'=>Field::TYPE_TIMESTAMP,
            'subtype'=>'',
            'readonly'=>false,
            'default'=>array(
                "on"=>"null",
                "value"=>"now",
            )
        ),

        // Length in seconds
        "duration" => array(
            'title'=>'Call Duration',
            'type'=>Field::TYPE_NUMBER,
            'subtype'=>'',
            'readonly'=>false
        ),

        // Customer
        "customer_id" => array(
            'title'=>'Contact',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'customer',
            'readonly'=>false,
        ),

        // Project
        "project_id" => array(
            'title'=>'Project',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'project',
            'readonly'=>false,
        ),

        // Case
        "case_id" => array(
            'title'=>'Case',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'case',
            'readonly'=>false,
        ),

        // Opportunities
        "opportunity_id" => array(
            'title'=>'Opportunity',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'opportunity',
            'readonly'=>false,
        ),

        // Opportunities
        "campaign_id" => array(
            'title'=>'Campaign',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'marketing_campaign',
            'readonly'=>false,
        ),

        // Lead
        "lead_id" => array(
            'title'=>'Lead',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'lead',
            'readonly'=>false,
        ),

        // Notes
        'notes' => array(
            'title'=>'Notes',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>false,
        ),

        // Status flag
        'purpose_id' => array(
            'title'=>'Purpose',
            'type'=>Field::TYPE_GROUPING,
            'subtype'=>'object_groupings',
        ),

        "owner_id" => array(
            'title'=>'Owner',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'user',
            'default'=>array(
                "value"=>"-3",
                "on"=>"null"
            ),
        ),
    ),
);
