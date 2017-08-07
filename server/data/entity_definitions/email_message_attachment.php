<?php
namespace data\entity_definitions;

return array(
    'revision' => 10,
    'is_private' => true,
    'default_activity_level' => 0,
    'fields' => array(
        'filename' => array(
            'title'=>'File Name',
            'type'=>'text',
            'subtype'=>'512',
            'readonly'=>true,
        ),
        'name' => array(
            'title'=>'Name',
            'type'=>'text',
            'subtype'=>'512',
            'readonly'=>true,
        ),
        'content_type' => array(
            'title'=>'Content Type',
            'type'=>'text',
            'subtype'=>'512',
            'readonly'=>true,
        ),
        'encoding' => array(
            'title'=>'Content Transfer Encoding',
            'type'=>'text',
            'subtype'=>'128',
            'readonly'=>true,
        ),
        'content_id' => array(
            'title'=>'Content Id',
            'type'=>'text',
            'subtype'=>'512',
            'readonly'=>true,
        ),
        'disposition' => array(
            'title'=>'Content Disposition',
            'type'=>'text',
            'subtype'=>'128',
            'readonly'=>true,
        ),
        'size' => array(
            'title'=>'Size',
            'type'=>'number',
            'subtype'=>'integer',
            'readonly'=>true
        ),
        'owner_id' => array(
            'title'=>'User',
            'type'=>'object',
            'subtype'=>'user',
            'default'=>array("value"=>"-3", "on"=>"null")
        ),
        'message_id' => array(
            'title'=>'Message',
            'type'=>'object',
            'subtype'=>'email_message',
            'required'=>true
        ),
        'file_id' => array(
            'title'=>'Download',
            'type'=>'object',
            'subtype'=>'file'
        ),
    ),
);
