<?php
namespace data\entity_definitions;
use Netric\EntityDefinition\Field;

return array(
    'store_revisions' => true,
    'parent_field' => 'parent_id',
    'icon' => 'folder',
    'fields' => array(
        'name' => array(
            'title'=>'Name',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'512',
            'readonly'=>false,
        ),
        'f_system' => array(
            'title'=>"System",
            'type'=>Field::TYPE_BOOL,
            'subtype'=>'',
            'readonly'=>true
        ),
        'parent_id' => array(
            'title'=>'Parent',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'folder'
        ),
        'owner_id' => array(
            'title'=>'Owner',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'user',
            'default'=>array("value"=>"-3", "on"=>"null"),
        ),
    ),
);
