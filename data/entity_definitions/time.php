<?php
namespace data\entity_definitions;
use Netric\EntityDefinition\Field;

return array(
    'fields' => array(
        'name' => array(
            'title'=>'Subject',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>false,
            'required'=>true
        ),
        'notes' => array(
            'title'=>'Notes',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>false
        ),
        'hours' => array(
            'title'=>'Hours',
            'type'=>Field::TYPE_NUMBER,
            'subtype'=>'double precision',
            'required'=>true,
            'readonly'=>false
        ),
        'date_applied' => array(
            'title'=>'Date',
            'type'=>Field::TYPE_DATE,
            'subtype'=>'',
            'readonly'=>false,
            'required'=>true
        ),
        'owner_id' => array(
            'title'=>'User',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'user',
            'required'=>true,
            'default'=>array("value"=>"-3", "on"=>"null")
        ),
        'creator_id' => array(
            'title'=>'Entered By',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'user',
            'readonly'=>true,
            'default'=>array("value"=>"-3", "on"=>"null")
        ),
        'task_id' => array(
            'title'=>'Task',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'task',
            'readonly'=>false
        ),
    ),
    'aggregates' => array(
        'incr_task_cost' => array(
            'type' => 'sum',
            'calc_field' => 'hours',
            'ref_obj_update' => 'task_id',
            'obj_field_to_update' => 'cost_actual',
        ),
    ),
);
