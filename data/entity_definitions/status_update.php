<?php
namespace data\entity_definitions;
use Netric\EntityDefinition\Field;

return array(
    'default_activity_level' => 5,
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
        'notify' => array(
            'title'=>'Send To',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>true
        ),
        'obj_reference' => array(
            'title'=>'Concerning',
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
    ),
);
