<?php
namespace data\entity_definitions;

return array(
    'fields' => array(
        'name' => array(
            'title'=>'Description',
            'type'=>'text',
            'subtype'=>'256',
            'readonly'=>false,
            'required'=>true
        ),
        'notes' => array(
            'title'=>'Notes',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>false
        ),
        'hours' => array(
            'title'=>'Hours',
            'type'=>'number',
            'subtype'=>'double precision',
            'required'=>true,
            'readonly'=>false
        ),
        'date_applied' => array(
            'title'=>'Date',
            'type'=>'date',
            'subtype'=>'',
            'readonly'=>false,
            'required'=>true
        ),
        'owner_id' => array(
            'title'=>'User',
            'type'=>'object',
            'subtype'=>'user',
            'required'=>true,
            'default'=>array("value"=>"-3", "on"=>"null")
        ),
        'creator_id' => array(
            'title'=>'Entered By',
            'type'=>'object',
            'subtype'=>'user',
            'readonly'=>true,
            'default'=>array("value"=>"-3", "on"=>"null")
        ),
        'task_id' => array(
            'title'=>'Task',
            'type'=>'object',
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
