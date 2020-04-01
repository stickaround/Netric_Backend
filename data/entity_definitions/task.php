<?php
namespace data\entity_definitions;

use Netric\EntityDefinition\Field;
use Netric\Entity\ObjType\UserEntity;

return array(
    'inherit_dacl_ref' => 'project',
    'recur_rules' => array(
        "field_time_start"=>"",
        "field_time_end"=>"",
        "field_date_start"=>"deadline",
        "field_date_end"=>"deadline",
        "field_recur_id"=>"recur_id"
    ),
    'fields' => array(
        'name' => array(
            'title'=>'Name',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>false,
            'required'=>true
        ),
        'notes' => array(
            'title'=>'Notes',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>false
        ),
        'done' => array(
            'title'=>'Completed',
            'type'=>Field::TYPE_BOOL,
            'subtype'=>'',
            'readonly'=>false
        ),
        'entered_by' => array(
            'title'=>'Entered By',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'128',
            'readonly'=>true
        ),
        'cost_estimated' => array(
            'title'=>'Estimated Time',
            'type'=>Field::TYPE_NUMBER,
            'subtype'=>'double precision',
            'readonly'=>false
        ),
        'cost_actual' => array(
            'title'=>'Actual Time',
            'type'=>Field::TYPE_NUMBER,
            'subtype'=>'double precision',
            'readonly'=>true
        ),
        'date_entered' => array(
            'title'=>'Date Entered',
            'type'=>Field::TYPE_DATE,
            'subtype'=>'',
            'readonly'=>true,
            'default'=>array("value"=>"now", "on"=>"create")
        ),
        'date_completed' => array(
            'title'=>'Date Completed',
            'type'=>Field::TYPE_DATE,
            'subtype'=>'',
            'readonly'=>false
        ),
        'deadline' => array(
            'title'=>'Due Date',
            'type'=>Field::TYPE_DATE,
            'subtype'=>'',
            'readonly'=>false
        ),
        'start_date' => array(
            'title'=>'Start Date',
            'type'=>Field::TYPE_DATE,
            'subtype'=>'',
            'readonly'=>false
        ),
        'milestone_id' => array(
            'title'=>'Milestone',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'project_milestone',
            "filter"=>array("project"=>"project_id"), // this.project = project_milestone.project_id
            'fkey_table'=>array("key"=>"id", "title"=>"name")
        ),
        'depends_task_id' => array(
            'title'=>'Depends On',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'task',
            // this.project = project_milestone.project_id
            "filter"=>array("project_id"=>"project_id"),
            'fkey_table'=>array("key"=>"id", "title"=>"name")
        ),
        'priority_id' => array(
            'title'=>'Priority',
            'type'=>Field::TYPE_GROUPING,
            'subtype'=>'object_groupings',
        ),
        'status_id' => array(
            'title'=>'Status',
            'type'=>Field::TYPE_GROUPING,
            'subtype'=>'object_groupings',
        ),
        'project' => array(
            'title'=>'Project',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'project'
        ),
        'contact_id' => array(
            'title'=>'Contact',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'contact_personal'
        ),
        'customer_id' => array(
            'title'=>'Contact',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'customer'
        ),
        'recur_id' => array(
            'title'=>'Recurrence',
            'readonly'=>true,
            'type'=>Field::TYPE_INTEGER,
        ),
        'obj_reference' => array(
            'title'=>'Reference',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'',
            'readonly'=>true
        ),
        'type_id' => array(
            'title'=>'Type',
            'type'=>Field::TYPE_GROUPING,
            'subtype'=>'object_groupings',
        ),
    ),
    'aggregates' => array(
        'incr_story_cost' => array(
            'type' => 'sum',
            'calc_field' => 'cost_actual',
            'ref_obj_update' => 'story_id',
            'obj_field_to_update' => 'cost_actual',
        ),
    ),
);
