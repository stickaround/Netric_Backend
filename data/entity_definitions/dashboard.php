<?php
namespace data\entity_definitions;

return array(
    'uname_settings' => 'owner_id:name',
    'fields' => array(
        'name' => array(
            'title'=>'Name',
            'type'=>'text',
            'subtype'=>'512',
            'required'=>true,
            'readonly'=>false
        ),
        'description' => array(
            'title'=>'Description',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>false
        ),
        'app_dash' => array(
            'title'=>'Application Dashboard',
            'type'=>'text',
            'subtype'=>'256',
            'readonly'=>true
        ),
        'layout' => array(
            'title'=>'Layout',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>true
        ),
        'num_columns' => array(
            'title'=>'Num Columns',
            'type'=>'number',
            'subtype'=>'',
            'optional_values'=>array("1"=>"One", "2"=>"Two", "3"=>"Three")
        ),
        'scope' => array(
            'title'=>'Scope',
            'type'=>'text',
            'subtype'=>'32',
            'optional_values'=>array("system"=>"System/Everyone", "user"=>"User")
        ),
        'groups' => array(
            'title'=>'Groups',
            'type'=>'fkey_multi',
            'subtype'=>'object_groupings',
            'fkey_table'=>array("key"=>"id", "title"=>"name", "parent"=>"parent_id",
                "ref_table"=>array(
                    "table"=>"object_grouping_mem",
                    "this"=>"object_id",
                    "ref"=>"grouping_id"
                ),
            ),
        ),
        'owner_id' => array(
            'title'=>'Owner',
            'type'=>'object',
            'subtype'=>'user',
            'default'=>array("value"=>-3, "on"=>"null")
        ),
    ),
);
