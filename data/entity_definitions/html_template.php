<?php
namespace data\entity_definitions;

return array(
    'fields' => array(
        'name' => array(
            'title'=>'Name',
            'type'=>'text',
            'subtype'=>'512',
            'readonly'=>false
        ),
        'subject' => array(
            'title'=>'Subject',
            'type'=>'text',
            'subtype'=>'512',
            'readonly'=>false
        ),
        'body_html' => array(
            'title'=>'Html Body',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>false
        ),
        'body_plain' => array(
            'title'=>'Plain Body',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>false
        ),
        'obj_type' => array(
            'title'=>'Type',
            'type'=>'text',
            'subtype'=>'128',
            'optional_values'=>array(
                "email_message"=>"Email",
                "content_feed_post"=>"Content Post"
            ),
        ),
        'owner_id' => array(
            'title' => 'Owner',
            'type' => 'object',
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
            'type' => 'text',
            'subtype' => '32',
            'optional_values' => array(
                "system"=>"System/Everyone",
                "user"=>"User"
            ),
        ),
        'groups' => array(
            'title'=>'Groups',
            'type'=>'fkey_multi',
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
