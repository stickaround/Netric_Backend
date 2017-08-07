<?php
namespace data\entity_definitions;

return array(
    'revision' => 16,
    'fields' => array(
        'name' => array(
            'title'=>'Title',
            'type'=>'text',
            'subtype'=>'512',
            'readonly'=>false
        ),
        'notes' => array(
            'title'=>'Description',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>false
        ),
        'date_start' => array(
            'title'=>'Date Start',
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
            'title'=>'Estimated Time',
            'type'=>'number',
            'subtype'=>'',
            'readonly'=>false
        ),
        'cost_actual' => array(
            'title'=>'Actual Time',
            'type'=>'number',
            'subtype'=>'',
            'readonly'=>false
        ),
        'project_id' => array(
            'title'=>'Project',
            'type'=>'object',
            'subtype'=>'project'
        ),
        'milestone_id' => array(
            'title'=>'Milestone',
            'type'=>'object',
            'subtype'=>'project_milestone'
        ),
        'owner_id' => array(
            'title'=>'Owner',
            'type'=>'object',
            'subtype'=>'user',
            'default'=>array("value"=>"-3", "on"=>"null"),
        ),
        'customer_id' => array(
            'title'=>'Contact',
            'type'=>'object',
            'subtype'=>'customer'
        ),
        'priority_id' => array(
            'title'=>'Priority',
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
    ),
);
