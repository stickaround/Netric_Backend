<?php
namespace data\entity_definitions;

use Netric\EntityDefinition\Field;

return array(
    'fields' => array(
        'object_type_id' => array(
            'title'=>'Object Type Id',
            'type'=>Field::TYPE_INTEGER,
            'subtype'=>'',
            'readonly'=>false,
            'required'=>true
        ),
        'object_type' => array(
            'title'=>'Object Type',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>false,
        ),
        'object_uid' => array(
            'title'=>'Object Uid',
            'type'=>Field::TYPE_INTEGER,
            'subtype'=>'',
            'readonly'=>false,
            'required'=>true
        ),
        'workflow_id' => array(
            'title'=>'Workflow Id',
            'type'=>Field::TYPE_INTEGER,
            'subtype'=>'',
            'readonly'=>false
        ),
        'ts_started' => array(
            'title'=>'Entered By',
            'type'=>Field::TYPE_TIMESTAMP,
            'subtype'=>'',
            'readonly'=>false,
        ),
        'ts_completed' => array(
            'title'=>'Completed',
            'type'=>Field::TYPE_TIMESTAMP,
            'subtype'=>'',
            'readonly'=>false
        ),
        'f_completed' => array(
            'title'=>'Actual Time',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => false,
            'default' => array(
                "value"=>'f',
                "on"=>"null",
            ),
        )
    )
);
