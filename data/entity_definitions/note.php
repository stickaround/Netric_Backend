<?php
namespace data\entity_definitions;

return array(
    'is_private' => true,
    'default_activity_level' => 1,
    'fields' => array(
        'name' => array(
            'title'=>'Title',
            'type'=>'text',
            'subtype'=>'256',
            'readonly'=>false
        ),
        'website' => array(
            'title'=>'Website',
            'type'=>'text',
            'subtype'=>'512',
            'readonly'=>false
        ),
        'body' => array(
            'title'=>'Body',
            'type'=>'text',
            'subtype'=>'html',
            'readonly'=>false
        ),
        'body_type' => array(
            'title'=>'Type',
            'type'=>'text',
            'subtype'=>'32',
            'readonly'=>true,
            'default'=>array("value"=>"html", "on"=>"null")
        ),
        'user_id' => array(
            'title'=>'User',
            'type'=>'object',
            'subtype'=>'user',
            'readonly'=>true,
            'default'=>array("value"=>"-3", "on"=>"null")
        ),
        'groups' => array(
            'title'=>'Groups',
            'type'=>'fkey_multi',
            'subtype'=>'object_groupings',
            'fkey_table'=>array(
                "key"=>"id",
                "title"=>"name",
                "parent"=>"parent_id",
                "filter"=>array(
                    "user_id"=>"user_id"
                ),
                "ref_table"=>array(
                    "table"=>"object_grouping_mem",
                    "this"=>"note_id",
                    "ref"=>"category_id"
                ),
            ),
        ),
    ),
);
