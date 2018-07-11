<?php
namespace data\entity_definitions;
use Netric\EntityDefinition\Field;

return array(
    'is_private' => false,
    'store_revisions' => false,
    'recur_rules' => array(
        "field_time_start"=>"",
        "field_time_end"=>"",
        "field_date_start"=>"ts_scheduled",
        "field_date_end"=>"ts_scheduled",
        "field_recur_id"=>"recur_id",
    ),
    'fields' => array(
        'worker_name' => array(
            'title'=>'Worker',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>true,
            'require'=>true
        ),
        'job_data' => array(
            'title'=>'Job Data',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>true,
            'require'=>true
        ),
        'ts_scheduled' => array(
            'title'=>'When Scheduled',
            'type'=>Field::TYPE_TIMESTAMP,
            'subtype'=>'',
            'readonly'=>true,
            'require'=>true
        ),
        'ts_executed' => array(
            'title'=>'Time Ran',
            'type'=>Field::TYPE_TIMESTAMP,
            'subtype'=>'',
            'readonly'=>true,
            'require'=>true
        ),
        'recur_id' => array(
            'title'=>'Recurrence',
            'readonly'=>true,
            'type'=>Field::TYPE_INTEGER,
        ),
    ),
);
