<?php
namespace data\entity_definitions;

return array(
    'revision' => 9,
    'default_activity_level' => 5,
    'fields' => array(
        // Textual name
        'name' => array(
            'title'=>'Subject',
            'type'=>'text',
            'subtype'=>'512',
            'readonly'=>false,
        ),

        "direction" => array(
            'title'=>'Direction',
            'type'=>'text',
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
            'type'=>'text',
            'subtype'=>'512',
            'readonly'=>false,
        ),

        "ts_start" => array(
            'title'=>'Call Start Time',
            'type'=>'timestamp',
            'subtype'=>'',
            'readonly'=>false,
            'default'=>array(
                "on"=>"null",
                "value"=>"now",
            )
        ),

        // Length in seconds
        "duration" => array(
            'title'=>'Call Duration', 'type'=>'number', 'subtype'=>'', 'readonly'=>false
        ),

        // Customer
        "customer_id" => array(
            'title'=>'Contact',
            'type'=>'object',
            'subtype'=>'customer',
            'readonly'=>false,
        ),

        // Project
        "project_id" => array(
            'title'=>'Project',
            'type'=>'object',
            'subtype'=>'project',
            'readonly'=>false,
        ),

        // Case
        "case_id" => array(
            'title'=>'Case',
            'type'=>'object',
            'subtype'=>'case',
            'readonly'=>false,
        ),

        // Opportunities
        "opportunity_id" => array(
            'title'=>'Opportunity',
            'type'=>'object',
            'subtype'=>'opportunity',
            'readonly'=>false,
        ),

        // Opportunities
        "campaign_id" => array(
            'title'=>'Campaign',
            'type'=>'object',
            'subtype'=>'marketing_campaign',
            'readonly'=>false,
        ),

        // Lead
        "lead_id" => array(
            'title'=>'Lead',
            'type'=>'object',
            'subtype'=>'lead',
            'readonly'=>false,
        ),

        // Notes
        'notes' => array(
            'title'=>'Notes',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>false,
        ),

        // Status flag
        'purpose_id' => array(
            'title'=>'Purpose',
            'type'=>'fkey',
            'subtype'=>'object_groupings',
            'fkey_table'=>array(
                "key"=>"id",
                "title"=>"name",
                "ref_table"=>array(
                    "table"=>"object_grouping_mem",
                    "this"=>"object_id",
                    "ref"=>"grouping_id"
                )
            )
        ),

        "owner_id" => array(
            'title'=>'Owner',
            'type'=>'object',
            'subtype'=>'user',
            'default'=>array(
                "value"=>"-3",
                "on"=>"null"
            ),
        ),
    ),
);
