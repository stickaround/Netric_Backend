<?php
namespace data\entity_definitions;

return array(
    'default_activity_level' => 5,
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
        'notify' => array(
            'title'=>'Send To',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>true
        ),
        'owner_id' => array(
            'title'=>'User',
            'type'=>'object',
            'subtype'=>'user',
            'default'=>array("value"=>"-3", "on"=>"null")
        ),
        'obj_reference' => array(
            'title'=>'Concerning',
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
    ),
);
