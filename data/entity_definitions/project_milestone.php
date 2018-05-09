<?php
namespace data\entity_definitions;
use Netric\EntityDefinition\Field;

return array(
    'inherit_dacl_ref' => "project_id",
    'fields' => array(
        'name' => array(
            'title'=>'Name',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>false
        ),
        'notes' => array(
            'title'=>'Description',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>false
        ),
        'f_completed' => array(
            'title'=>'Completed',
            'type'=>Field::TYPE_BOOL,
            'subtype'=>'',
            'readonly'=>false,
            'default'=>array("value"=>"f", "on"=>"null")
        ),
        'date_start' => array(
            'title'=>'Date Start',
            'type'=>Field::TYPE_DATE,
            'subtype'=>'',
            'readonly'=>false,
            'default'=>array("value"=>"now", "on"=>"null")
        ),
        'deadline' => array(
            'title'=>'Deadline',
            'type'=>Field::TYPE_DATE,
            'subtype'=>'',
            'readonly'=>false
        ),
        'project_id' => array(
            'title'=>'Project',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'project',
            'fkey_table'=>array("key"=>"id", "title"=>"name", "parent"=>"parent")
        ),
        'user_id' => array(
            'title'=>'Owner',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'user',
            'default'=>array("value"=>"-3", "on"=>"null")
        ),
    ),
);
