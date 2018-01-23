<?php
namespace data\entity_definitions;
use Netric\EntityDefinition\Field;

return array(
    'fields' => array(
        'name' => array(
            'title'=>'Name',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'512',
            'readonly'=>false
        ),
        'subject' => array(
            'title'=>'Subject',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'512',
            'readonly'=>false
        ),
        'body_html' => array(
            'title'=>'Html Body',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>false
        ),
        'body_plain' => array(
            'title'=>'Plain Body',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>false
        ),
        'obj_type' => array(
            'title'=>'Type',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'128',
            'optional_values'=>array(
                "email_message"=>"Email",
                "content_feed_post"=>"Content Post"
            ),
        ),
        'owner_id' => array(
            'title' => 'Owner',
            'type'=>Field::TYPE_OBJECT,
            'subtype' => 'user',
            'readonly' => false,
            'require' => true,
            'default' => array(
                "on" => "null",
                "value" => "-3",
            ),
        ),
        'scope' => array(
            'title' => 'Scope',
            'type'=>Field::TYPE_TEXT,
            'subtype' => '32',
            'optional_values' => array(
                "system"=>"System/Everyone",
                "user"=>"User"
            ),
        ),
        'groups' => array(
            'title'=>'Groups',
            'type'=>Field::TYPE_GROUPING_MULTI,
            'subtype'=>'object_groupings',
            'fkey_table'=>array(
                "key"=>"id",
                "title"=>"name",
                "parent"=>"parent_id",
                "ref_table"=>array(
                    "table"=>"object_grouping_mem",
                    "this"=>"object_id",
                    "ref"=>"grouping_id"
                )
            )
        ),
    ),
);
