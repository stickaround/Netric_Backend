<?php
namespace data\entity_definitions;

return array(
    'revision' => 2,
    'is_private' => false,
    'store_revisions' => false,
    'recur_rules' => array(
        "field_time_start"=>"ts_scheduled",
        "field_time_end"=>"ts_scheduled",
        "field_date_start"=>"ts_scheduled",
        "field_date_end"=>"ts_scheduled",
        "field_recur_id"=>"recur_id",
    ),
    'fields' => array(
        'worker_name' => array(
            'title'=>'Worker',
            'type'=>'text',
            'subtype'=>'256',
            'readonly'=>true,
            'require'=>true
        ),
        'job_data' => array(
            'title'=>'Job Data',
            'type'=>'text',
            'subtype'=>'256',
            'readonly'=>true,
            'require'=>true
        ),
        'ts_scheduled' => array(
            'title'=>'When Scheduled',
            'type'=>'timestamp',
            'subtype'=>'',
            'readonly'=>true,
            'require'=>true
        ),
        'ts_executed' => array(
            'title'=>'When Ran',
            'type'=>'timestamp',
            'subtype'=>'',
            'readonly'=>true,
            'require'=>true
        ),
        'recur_id' => array(
            'title'=>'Recurrence',
            'readonly'=>true,
            'type'=>'integer'
        ),
    ),
);