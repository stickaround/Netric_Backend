<?php
namespace data\entity_definitions;
use Netric\EntityDefinition\Field;

return array(
    'parent_field' => 'parent_id',
    'fields' => array(
        'name' => array(
            'title'=>'Title',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'512',
            'readonly'=>false,
            'required'=>true
        ),
        'description' => array(
            'title'=>'Description',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>false
        ),
        'date_start' => array(
            'title'=>'Start Date',
            'type'=>Field::TYPE_DATE,
            'subtype'=>'',
            'readonly'=>false
        ),
        'date_end' => array(
            'title'=>'End Date',
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
            'title'=>'Estimated Cost',
            'type'=>Field::TYPE_NUMBER,
            'subtype'=>'',
            'readonly'=>false
        ),
        'cost_actual' => array(
            'title'=>'Actual Cost',
            'type'=>Field::TYPE_NUMBER,
            'subtype'=>'',
            'readonly'=>false
        ),
        'rev_estimated' => array(
            'title'=>'Estimated Revenue',
            'type'=>Field::TYPE_NUMBER,
            'subtype'=>'',
            'readonly'=>false
        ),
        'rev_actual' => array(
            'title'=>'Actual Revenue',
            'type'=>Field::TYPE_NUMBER,
            'subtype'=>'',
            'readonly'=>false
        ),
        'num_sent' => array(
            'title'=>'Number Sent',
            'type'=>Field::TYPE_NUMBER,
            'subtype'=>'',
            'readonly'=>false
        ),
        'resp_estimated' => array(
            'title'=>'Estimated Response %',
            'type'=>Field::TYPE_NUMBER,
            'subtype'=>'',
            'readonly'=>false
        ),
        'resp_actual' => array(
            'title'=>'Actual Response %',
            'type'=>Field::TYPE_NUMBER,
            'subtype'=>'',
            'readonly'=>false
        ),
        'email_opens' => array(
            'title'=>'Opens',
            'type'=>Field::TYPE_NUMBER,
            'subtype'=>'',
            'readonly'=>false,
            "default"=>array("value"=>"0", "on"=>"null")
        ),
        'email_unsubscribers' => array(
            'title'=>'Unsubscribers',
            'type'=>Field::TYPE_NUMBER,
            'subtype'=>'',
            'readonly'=>false,
            "default"=>array("value"=>"0", "on"=>"null")
        ),
        'email_bounced' => array(
            'title'=>'Bounced',
            'type'=>Field::TYPE_NUMBER,
            'subtype'=>'',
            'readonly'=>false,
            "default"=>array("value"=>"0", "on"=>"null")
        ),
        'type_id' => array(
            'title'=>'Type',
            'type'=>Field::TYPE_GROUPING,
            'subtype'=>'object_groupings',
        ),
        'status_id' => array(
            'title'=>'Status',
            'type'=>Field::TYPE_GROUPING,
            'subtype'=>'object_groupings',
        ),
        'parent_id' => array(
            'title'=>'Parent Campaign',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'marketing_campaign'
        ),
        'email_campaign_id' => array(
            'title'=>'Email Campaign',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'email_campaign'
        ),
    ),
);
