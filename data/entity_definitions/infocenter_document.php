<?php
namespace data\entity_definitions;

return array(
    'fields' => array(
        'title' => array(
            'title'=>'Title',
            'type'=>'text',
            'subtype'=>'512',
            'readonly'=>false
        ),
        'keywords' => array(
            'title'=>'Keywords',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>false
        ),
        'author_name' => array(
            'title'=>'Author Names',
            'type'=>'text',
            'subtype'=>'64',
            'readonly'=>false
        ),
        'body' => array(
            'title'=>'Body',
            'type'=>'text',
            'subtype'=>'html',
            'readonly'=>false
        ),
        'rating' => array(
            'title'=>'Rating',
            'type'=>'number',
            'subtype'=>'integer',
            'readonly'=>false
        ),
        'video_file_id' => array(
            'title'=>'Video File',
            'type'=>'object',
            'subtype'=>'file',
            'fkey_table'=>array("key"=>"id", "title"=>"file_title")
        ),
        'owner_id' => array(
            'title'=>'Owner',
            'type'=>'object',
            'subtype'=>'user',
            'readonly'=>false,
            'default'=>array("value"=>"-3", "on"=>"null")
        ),
        'groups' => array(
            'title'=>'Groups',
            'type'=>'fkey_multi',
            'subtype'=>'object_groupings',
            'fkey_table'=>array("key"=>"id", "title"=>"name", "parent"=>"parent_id",
                "ref_table"=>array(
                    "table"=>"object_grouping_mem",
                    "this"=>"document_id",
                    "ref"=>"group_id"
                ),
            ),
        ),
    ),
);
