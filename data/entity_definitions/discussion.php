<?php
namespace data\entity_definitions;
use Netric\EntityDefinition\Field;

return array(
    'default_activity_level' => 5,
    'fields' => array(
        'name' => array(
            'title'=>'Subject / Topic',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'512',
            'readonly'=>false
        ),
        'message' => array(
            'title'=>'Message',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>false
        ),
        'notified' => array(
            'title'=>'Invited',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>true
        ),
        'notify' => array(
            'title'=>'Invite',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>true
        ),
        'owner_id' => array(
            'title'=>'User',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'user',
            'readonly'=>true,
            'default'=>array("value"=>"-3", "on"=>"null")
        ),
        'obj_reference' => array(
            'title'=>'Concerning',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'',
            'readonly'=>true
        ),
        'members' => array(
            'title'=>'Notify',
            'type'=>Field::TYPE_OBJECT_MULTI,
            'subtype'=>'user'
        ),
    ),
);
