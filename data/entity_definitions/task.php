<?php
namespace data\entity_definitions;

return array(
    'inherit_dacl_ref' => 'project',
    'recur_rules' => array(
        "field_time_start"=>"",
        "field_time_end"=>"",
        "field_date_start"=>"start_date",
        "field_date_end"=>"deadline",
        "field_recur_id"=>"recur_id"
    ),
    'fields' => array(
        'name' => array(
            'title'=>'Name',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>false,
            'required'=>true
        ),
        'notes' => array(
            'title'=>'Notes', 'type'=>'text', 'subtype'=>'', 'readonly'=>false
        ),
        'done' => array(
            'title'=>'Completed', 'type'=>'bool', 'subtype'=>'', 'readonly'=>false
        ),
        'entered_by' => array(
            'title'=>'Entered By', 'type'=>'text', 'subtype'=>'128', 'readonly'=>true
        ),
        'cost_estimated' => array(
            'title'=>'Estimated Time', 'type'=>'number', 'subtype'=>'double precision', 'readonly'=>false
        ),
        'cost_actual' => array(
            'title'=>'Actual Time', 'type'=>'number', 'subtype'=>'double precision', 'readonly'=>true
        ),
        'date_entered' => array(
            'title'=>'Date Entered', 'type'=>'date', 'subtype'=>'', 'readonly'=>true, 'default'=>array("value"=>"now", "on"=>"create")
        ),
        'date_completed' => array(
            'title'=>'Date Completed', 'type'=>'date', 'subtype'=>'', 'readonly'=>true, 'default'=>array("value"=>"now", "on"=>"null", "where"=>array("done"=>'t'))
        ),
        'deadline' => array(
            'title'=>'Date Due', 'type'=>'date', 'subtype'=>'', 'readonly'=>false
        ),
        'start_date' => array(
            'title'=>'Start Date', 'type'=>'date', 'subtype'=>'', 'readonly'=>false
        ),
        'milestone_id' => array(
            'title'=>'Milestone',
            'type'=>'object',
            'subtype'=>'project_milestone',
            "filter"=>array("project"=>"project_id"), // this.project = project_milestone.project_id
            'fkey_table'=>array("key"=>"id", "title"=>"name")
        ),
        'depends_task_id' => array(
            'title'=>'Depends On',
            'type'=>'object',
            'subtype'=>'task',
            // this.project = project_milestone.project_id
            "filter"=>array("project_id"=>"project_id"),
            'fkey_table'=>array("key"=>"id", "title"=>"name")
        ),
        'user_id' => array(
            'title'=>'Assigned To',
            'type'=>'object',
            'subtype'=>'user',
            'default'=>array("value"=>"-3", "on"=>"null")
        ),
        'creator_id' => array(
            'title'=>'Creator',
            'type'=>'object',
            'subtype'=>'user',
            'readonly'=>true,
            'default'=>array("value"=>"-3", "on"=>"null")
        ),
        'priority' => array(
            'title'=>'Priority',
            'type'=>'fkey',
            'subtype'=>'project_priorities',
            'fkey_table'=>array("key"=>"id", "title"=>"name")
        ),
        'project' => array(
            'title'=>'Project',
            'type'=>'object',
            'subtype'=>'project'
        ),
        'case_id' => array(
            'title'=>'Case',
            'type'=>'object',
            'subtype'=>'case'
        ),
        'contact_id' => array(
            'title'=>'Contact',
            'type'=>'object',
            'subtype'=>'contact_personal'
        ),
        'customer_id' => array(
            'title'=>'Contact',
            'type'=>'object',
            'subtype'=>'customer'
        ),
        'story_id' => array(
            'title'=>'Story',
            'type'=>'object',
            'subtype'=>'project_story'
        ),
        'category' => array(
            'title'=>'Category',
            'type'=>'fkey',
            'private'=>true,
            'subtype'=>'object_groupings',
            'fkey_table'=>array(
                "key"=>"id",
                "title"=>"name",
                "parent"=>"parent_id",
                "filter"=>array("user_id"=>"user_id"),
                "ref_table"=>array(
                    "table"=>"object_grouping_mem",
                    "this"=>"object_id",
                    "ref"=>"grouping_id"
                ),
            ),
        ),
        'recur_id' => array(
            'title'=>'Recurrence',
            'readonly'=>true,
            'type'=>'integer'
        ),
        'obj_reference' => array(
            'title'=>'Reference',
            'type'=>'object',
            'subtype'=>'',
            'readonly'=>true
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
