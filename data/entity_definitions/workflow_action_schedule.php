<?php
namespace data\entity_definitions;

use Netric\EntityDefinition\Field;

return array(
    'fields' => array(
        'action_id' => array(
            'title'=>'Action Id',
            'type'=>Field::TYPE_INTEGER,
            'subtype'=>'',
            'readonly'=>false,
        ),
        'ts_execute' => array(
            'title'=>'Execute',
            'type'=>Field::TYPE_TIMESTAMP,
            'subtype'=>'',
            'readonly'=>false,
        ),
        'instance_id' => array(
            'title'=>'Instance Id',
            'type'=>Field::TYPE_INTEGER,
            'subtype'=>'',
            'readonly'=>false,
        ),
        'inprogress' => array(
            'title'=>'In Progress',
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
