<?php
namespace data\entity_definitions;

use Netric\EntityDefinition\Field;
use Netric\Entity\ObjType\UserEntity;

return array(
    'is_private' => true,
    'default_activity_level' => 1,
    'fields' => array(
        'name' => array(
            'title'=>'Title',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>false
        ),
        'website' => array(
            'title'=>'Website',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'512',
            'readonly'=>false
        ),
        'body' => array(
            'title'=>'Body',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'html',
            'readonly'=>false
        ),
        'body_type' => array(
            'title'=>'Type',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'32',
            'readonly'=>true,
            'default'=>array("value"=>"html", "on"=>"null")
        ),        
        'groups' => array(
            'title'=>'Groups',
            'type'=>Field::TYPE_GROUPING_MULTI,
            'subtype'=>'object_groupings',
        ),
    ),
);
