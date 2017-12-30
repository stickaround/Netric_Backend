<?php
namespace data\entity_definitions;

return array(
    'parent_field' => 'parent_id',
    'fields' => array(
        'name' => array(
            'title'=>'Title',
            'type'=>'text',
            'subtype'=>'512',
            'readonly'=>false,
            'required'=>true
        ),
        'description' => array(
            'title'=>'Description',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>false
        ),
        'date_start' => array(
            'title'=>'Start Date',
            'type'=>'date',
            'subtype'=>'',
            'readonly'=>false
        ),
        'date_end' => array(
            'title'=>'End Date',
            'type'=>'date',
            'subtype'=>'',
            'readonly'=>false
        ),
        'date_completed' => array(
            'title'=>'Date Completed',
            'type'=>'date',
            'subtype'=>'',
            'readonly'=>false
        ),
        'cost_estimated' => array(
            'title'=>'Estimated Cost',
            'type'=>'number',
            'subtype'=>'',
            'readonly'=>false
        ),
        'cost_actual' => array(
            'title'=>'Actual Cost',
            'type'=>'number',
            'subtype'=>'',
            'readonly'=>false
        ),
        'rev_estimated' => array(
            'title'=>'Estimated Revenue',
            'type'=>'number',
            'subtype'=>'',
            'readonly'=>false
        ),
        'rev_actual' => array(
            'title'=>'Actual Revenue',
            'type'=>'number',
            'subtype'=>'',
            'readonly'=>false
        ),
        'num_sent' => array(
            'title'=>'Number Sent',
            'type'=>'number',
            'subtype'=>'',
            'readonly'=>false
        ),
        'resp_estimated' => array(
            'title'=>'Estimated Response %',
            'type'=>'number',
            'subtype'=>'',
            'readonly'=>false
        ),
        'resp_actual' => array(
            'title'=>'Actual Response %',
            'type'=>'number',
            'subtype'=>'',
            'readonly'=>false
        ),
        'email_opens' => array(
            'title'=>'Opens',
            'type'=>'number',
            'subtype'=>'',
            'readonly'=>false,
            "default"=>array("value"=>"0", "on"=>"null")
        ),
        'email_unsubscribers' => array(
            'title'=>'Unsubscribers',
            'type'=>'number',
            'subtype'=>'',
            'readonly'=>false,
            "default"=>array("value"=>"0", "on"=>"null")
        ),
        'email_bounced' => array(
            'title'=>'Bounced',
            'type'=>'number',
            'subtype'=>'',
            'readonly'=>false,
            "default"=>array("value"=>"0", "on"=>"null")
        ),
        'type_id' => array(
            'title'=>'Type',
            'type'=>'fkey',
            'subtype'=>'object_groupings',
            'fkey_table'=>array("key"=>"id", "title"=>"name", "parent"=>"parent_id",
                "ref_table"=>array(
                    "table"=>"object_grouping_mem",
                    "this"=>"object_id",
                    "ref"=>"grouping_id"
                ),
            ),
        ),
        'status_id' => array(
            'title'=>'Status',
            'type'=>'fkey',
            'subtype'=>'object_groupings',
            'fkey_table'=>array("key"=>"id", "title"=>"name", "parent"=>"parent_id",
                "ref_table"=>array(
                    "table"=>"object_grouping_mem",
                    "this"=>"object_id",
                    "ref"=>"grouping_id"
                ),
            ),
        ),
        'parent_id' => array(
            'title'=>'Parent Campaign',
            'type'=>'object',
            'subtype'=>'marketing_campaign'
        ),
        'email_campaign_id' => array(
            'title'=>'Email Campaign',
            'type'=>'object',
            'subtype'=>'email_campaign'
        ),
    ),
);
