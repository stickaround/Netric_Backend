<?php
namespace data\entity_definitions;
use Netric\EntityDefinition\Field;

return array(
    'is_private' => true,
    'default_activity_level' => 0,
    'fields' => array(
        'filename' => array(
            'title'=>'File Name',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'512',
            'readonly'=>true,
        ),
        'name' => array(
            'title'=>'Name',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'512',
            'readonly'=>true,
        ),
        'content_type' => array(
            'title'=>'Content Type',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'512',
            'readonly'=>true,
        ),
        'encoding' => array(
            'title'=>'Content Transfer Encoding',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'128',
            'readonly'=>true,
        ),
        'content_id' => array(
            'title'=>'Content Id',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'512',
            'readonly'=>true,
        ),
        'disposition' => array(
            'title'=>'Content Disposition',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'128',
            'readonly'=>true,
        ),
        'size' => array(
            'title'=>'Size',
            'type'=>Field::TYPE_NUMBER,
            'subtype'=>'integer',
            'readonly'=>true
        ),
        'message_id' => array(
            'title'=>'Message',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'email_message',
            'required'=>true
        ),
        'file_id' => array(
            'title'=>'Download',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'file'
        ),
    ),
);
