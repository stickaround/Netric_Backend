<?php
namespace data\entity_definitions;
use Netric\EntityDefinition\Field;

return array(
    'fields' => array(
        'name' => array(
            'title'=>'Title',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'512',
            'readonly'=>false
        ),
        'notes' => array(
            'title'=>'Description',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>false
        ),
        'date_start' => array(
            'title'=>'Date Start',
            'type'=>Field::TYPE_DATE,
            'subtype'=>'',
            'readonly'=>false
        ),
        'date_completed' => array(
            'title'=>'Date Completed',
            'type'=>Field::TYPE_DATE,
            'subtype'=>'',
            'readonly'=>false
        ),
        'cost_estimated' => array(
            'title'=>'Estimated Time',
            'type'=>Field::TYPE_NUMBER,
            'subtype'=>'',
            'readonly'=>false
        ),
        'cost_actual' => array(
            'title'=>'Actual Time',
            'type'=>Field::TYPE_NUMBER,
            'subtype'=>'',
            'readonly'=>false
        ),
        'project_id' => array(
            'title'=>'Project',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'project'
        ),
        'milestone_id' => array(
            'title'=>'Milestone',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'project_milestone'
        ),
        'owner_id' => array(
            'title'=>'Owner',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'user',
            'default'=>array("value"=>"-3", "on"=>"null"),
        ),
        'customer_id' => array(
            'title'=>'Contact',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'customer'
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
        'type_id' => array(
            'title'=>'Type',
            'type'=>Field::TYPE_GROUPING,
            'subtype'=>'object_groupings',
        ),
    ),
);
