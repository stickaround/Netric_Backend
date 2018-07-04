<?php
namespace data\entity_definitions;
use Netric\EntityDefinition\Field;

return array(
    'uname_settings' => 'name',
    'fields' => array(
        'name' => array(
            'title'=>'Name',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'512',
            'required'=>true,
            'readonly'=>false
        ),
        'description' => array(
            'title'=>'Description',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>false
        ),
        'layout' => array(
            'title'=>'Layout',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>true
        ),
        'num_columns' => array(
            'title'=>'Number Columns',
            'type'=>Field::TYPE_NUMBER,
            'subtype'=>'',
            'optional_values'=>array("1"=>"One", "2"=>"Two", "3"=>"Three")
        ),
        'scope' => array(
            'title'=>'Scope',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'32',
            'optional_values'=>array("system"=>"System/Everyone", "user"=>"User")
        ),
        'groups' => array(
            'title'=>'Groups',
            'type'=>Field::TYPE_GROUPING_MULTI,
            'subtype'=>'object_groupings',
        ),
    ),
);
