<?php
namespace data\entity_definitions;

return array(
    'fields' => array(
        'dashboard_id' => array(
            'title' => 'Dashboard',
            'type' => 'object',
            'subtype' => 'dashboard'
        ),
        'widget_name' => array(
            'title'=>'Widget Name',
            'type'=>'text',
            'subtype'=>'256',
            'readonly'=>true
        ),
        'col' => array(
            'title'=>'Column',
            'type'=>'number',
            'subtype'=>'',
            'required'=>true,
            'readonly'=>false
        ),
        'pos' => array(
            'title'=>'Position',
            'type'=>'number',
            'subtype'=>'',
            'required'=>true,
            'readonly'=>false
        ),
        'data' => array(
            'title'=>'Data',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>true,
            'default'=>array("value"=>"", "on"=>"null")
        ),
    ),
);
