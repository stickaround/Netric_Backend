<?php
namespace data\entity_definitions;

return array(
    'fields' => array(
        'comment' => array(
            'title'=>'Comment',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>true
        ),
        'notified' => array(
            'title'=>'Notified',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>true
        ),
        // Object data comma separated
        'notify' => array(
            'title'=>'Send To',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>true
        ),
        'ts_entered' => array(
            'title'=>'Date', '
            type'=>'timestamp',
            'subtype'=>'',
            'readonly'=>true,
            'default'=>array(
                "value"=>"now",
                "on"=>"create"
            ),
        ),
        'owner_id' => array(
            'title'=>'User',
            'type'=>'object',
            'subtype'=>'user',
            'default'=>array(
                "value"=>"-3",
                "on"=>"null"
            ),
        ),
        'obj_reference' => array(
            'title'=>'Reference',
            'type'=>'object',
            'subtype'=>'',
            'readonly'=>true
        ),
        'sent_by' => array(
            'title'=>'Sent By',
            'type'=>'object',
            'subtype'=>'',
            'readonly'=>true
        ),
        'attachments' => array(
            'title'=>'Attachments',
            'type'=>'object_multi',
            'subtype'=>'file',
        ),
    ),
);
