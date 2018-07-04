<?php
namespace data\entity_definitions;
use Netric\EntityDefinition\Field;

return array(
    'fields' => array(
        'comment' => array(
            'title'=>'Comment',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>true
        ),
        'notified' => array(
            'title'=>'Notified',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>true
        ),
        // Object data comma separated
        'notify' => array(
            'title'=>'Sent To',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>true
        ),
        'ts_entered' => array(
            'title'=>'Date',
            'type'=>Field::TYPE_TIMESTAMP,
            'subtype'=>'',
            'readonly'=>true,
            'default'=>array(
                "value"=>"now",
                "on"=>"create"
            ),
        ),
        'obj_reference' => array(
            'title'=>'Reference',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'',
            'readonly'=>true
        ),
        'sent_by' => array(
            'title'=>'Sent By',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'',
            'readonly'=>true
        ),
        'attachments' => array(
            'title'=>'Attachments',
            'type'=>Field::TYPE_OBJECT_MULTI,
            'subtype'=>'file',
        ),
    ),
);
