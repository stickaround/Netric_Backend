<?php
namespace data\entity_definitions;
use Netric\EntityDefinition\Field;

return array(
    'is_private' => true,
    'default_activity_level' => 1,
    'fields' => array(
        'name' => array(
            'title'=>'Title',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>false
        ),
        'website' => array(
            'title'=>'Website',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'512',
            'readonly'=>false
        ),
        'body' => array(
            'title'=>'Body',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'html',
            'readonly'=>false
        ),
        'body_type' => array(
            'title'=>'Type',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'32',
            'readonly'=>true,
            'default'=>array("value"=>"html", "on"=>"null")
        ),
        'user_id' => array(
            'title'=>'User',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'user',
            'readonly'=>true,
            'default'=>array("value"=>"-3", "on"=>"null")
        ),
        'groups' => array(
            'title'=>'Groups',
            'type'=>Field::TYPE_GROUPING_MULTI,
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
                    "this"=>"object_id",
                    "ref"=>"grouping_id"
                ),
            ),
        ),
    ),
);
