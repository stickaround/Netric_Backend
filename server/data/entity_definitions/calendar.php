<?php
namespace data\entity_definitions;

return array(
    'revision' => 15,
    'fields' => array(
        'name' => array(
            'title'=>'Name',
            'type'=>'text',
            'subtype'=>'512',
            'readonly'=>false,
        ),
        'f_public' => array(
            'title'=>'Public',
            'type'=>'bool',
            'subtype'=>'',
            'readonly'=>false,
            'default'=> array(
                "on"=>"null",
                "value"=>"f",
            ),
        ),
        'f_view' => array(
            'title'=>'Visible',
            'type'=>'bool',
            'subtype'=>'',
            'readonly'=>false
        ),
        'def_cal' => array(
            'title'=>'Default',
            'type'=>'bool',
            'subtype'=>'',
            'readonly'=>false
        ),
        'user_id' => array('title'=>'User',
            'type'=>'object',
            'subtype'=>'user',
            'default'=>array(
                "value"=>-3,
                "on"=>"null"
            ),
        ),
        'owner_id' => array('title'=>'Owner',
            'type'=>'object',
            'subtype'=>'user',
            'default'=>array(
                "value"=>-3,
                "on"=>"null"
            ),
        ),
    ),
);
