<?php
namespace data\entity_definitions;

return array(
    'revision' => 11,
    'inherit_dacl_ref' => "project_id",
    'fields' => array(
        'name' => array(
            'title'=>'Name',
            'type'=>'text',
            'subtype'=>'256',
            'readonly'=>false
        ),
        'notes' => array(
            'title'=>'Notes',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>false
        ),
        'f_completed' => array(
            'title'=>'Completed',
            'type'=>'bool',
            'subtype'=>'',
            'readonly'=>false,
            'default'=>array("value"=>"f", "on"=>"null")
        ),
        'date_start' => array(
            'title'=>'Date Start',
            'type'=>'date',
            'subtype'=>'',
            'readonly'=>false,
            'default'=>array("value"=>"now", "on"=>"null")
        ),
        'deadline' => array(
            'title'=>'Deadline',
            'type'=>'date',
            'subtype'=>'',
            'readonly'=>false
        ),
        'project_id' => array(
            'title'=>'Project',
            'type'=>'object',
            'subtype'=>'project',
            'fkey_table'=>array("key"=>"id", "title"=>"name", "parent"=>"parent")
        ),
        'user_id' => array(
            'title'=>'Owner',
            'type'=>'object',
            'subtype'=>'user',
            'default'=>array("value"=>"-3", "on"=>"null")
        ),
    ),
);
