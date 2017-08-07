<?php
namespace data\entity_definitions;

return array(
    'revision' => 15,
    'default_activity_level' => 5,
    'fields' => array(
        'name' => array(
            'title'=>'Subject / Topic',
            'type'=>'text',
            'subtype'=>'512',
            'readonly'=>false
        ),
        'message' => array(
            'title'=>'Message',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>false
        ),
        'notified' => array(
            'title'=>'Invited',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>true
        ),
        'notify' => array(
            'title'=>'Invite',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>true
        ),
        'owner_id' => array(
            'title'=>'User',
            'type'=>'object',
            'subtype'=>'user',
            'readonly'=>true,
            'default'=>array("value"=>"-3", "on"=>"null")
        ),
        'obj_reference' => array(
            'title'=>'Concerning',
            'type'=>'object',
            'subtype'=>'',
            'readonly'=>true
        ),
        'members' => array(
            'title'=>'Notify',
            'type'=>'object_multi',
            'subtype'=>'user'
        ),
    ),
);
