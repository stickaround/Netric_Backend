<?php
namespace data\entity_definitions;
use Netric\EntityDefinition\Field;

return array(
    'fields' => array(
        'title' => array(
            'title'=>'Title',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'512',
            'readonly'=>false
        ),
        'keywords' => array(
            'title'=>'Keywords',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>false
        ),
        'author_name' => array(
            'title'=>'Author Name',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'64',
            'readonly'=>false
        ),
        'body' => array(
            'title'=>'Body',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'html',
            'readonly'=>false
        ),
        'rating' => array(
            'title'=>'Rating',
            'type'=>Field::TYPE_NUMBER,
            'subtype'=>'integer',
            'readonly'=>false
        ),
        'video_file_id' => array(
            'title'=>'Video File',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'file',
            'fkey_table'=>array("key"=>"id", "title"=>"file_title")
        ),
        'owner_id' => array(
            'title'=>'Owner',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'user',
            'readonly'=>false,
            'default'=>array("value"=>"-3", "on"=>"null")
        ),
        'groups' => array(
            'title'=>'Groups',
            'type'=>Field::TYPE_GROUPING_MULTI,
            'subtype'=>'object_groupings',
        ),
    ),
);
