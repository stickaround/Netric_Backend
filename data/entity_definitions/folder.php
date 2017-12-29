<?php
namespace data\entity_definitions;

return array(
    'revision' => 17,
    'store_revisions' => true,
    'parent_field' => 'parent_id',
    'icon' => 'folder',
    'fields' => array(
        'name' => array(
            'title'=>'Name',
            'type'=>'text',
            'subtype'=>'512',
            'readonly'=>false,
        ),
        'f_system' => array(
            'title'=>"System",
            'type'=>'bool',
            'subtype'=>'',
            'readonly'=>true
        ),
        'parent_id' => array(
            'title'=>'Parent',
            'type'=>'object',
            'subtype'=>'folder'
        ),
        'owner_id' => array(
            'title'=>'Owner',
            'type'=>'object',
            'subtype'=>'user',
            'default'=>array("value"=>"-3", "on"=>"null"),
        ),
    ),
);
