<?php
namespace data\entity_definitions;
use Netric\EntityDefinition\Field;

return array(
    'fields' => array(
        'dashboard_id' => array(
            'title' => 'Dashboard',
            'type'=>Field::TYPE_OBJECT,
            'subtype' => 'dashboard'
        ),
        'widget_name' => array(
            'title'=>'Widget Name',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>true
        ),
        'col' => array(
            'title'=>'Column',
            'type'=>Field::TYPE_NUMBER,
            'subtype'=>'',
            'required'=>true,
            'readonly'=>false
        ),
        'pos' => array(
            'title'=>'Position',
            'type'=>Field::TYPE_NUMBER,
            'subtype'=>'',
            'required'=>true,
            'readonly'=>false
        ),
        'data' => array(
            'title'=>'Data',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>true,
            'default'=>array("value"=>"", "on"=>"null")
        ),
    ),
);
