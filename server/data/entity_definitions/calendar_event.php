<?php
namespace data\entity_definitions;

return array(
    'revision' => 35,
    'fields' => array(
        'name' => array(
            'title'=>'Name',
            'type'=>'text',
            'subtype'=>'512',
            'readonly'=>false
        ),
        'location' => array(
            'title'=>'Location',
            'type'=>'text',
            'subtype'=>'512',
            'readonly'=>false
        ),
        'notes' => array(
            'title'=>'Notes',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>false
        ),
        'start_block' => array(
            'title'=>'Start Minute',
            'type'=>'integer',
            'subtype'=>'',
            'readonly'=>true
        ),
        'end_block' => array(
            'title'=>'End Minute',
            'type'=>'integer',
            'subtype'=>'',
            'readonly'=>true
        ),
        'all_day' => array(
            'title'=>'All Day',
            'type'=>'bool',
            'subtype'=>'',
            'readonly'=>false
        ),
        'sharing' => array(
            'title'=>'Sharing',
            'type'=>'integer',
            'subtype'=>'',
            'optional_values'=>array(
                "2"=>"Public",
                "1"=>"Private"
            ),
            'readonly'=>false
        ),
        'inv_eid' => array(
            'title'=>'Inv. Eid',
            'type'=>'integer',
            'subtype'=>'',
            'readonly'=>true
        ),
        'inv_rev' => array(
            'title'=>'Inv. Revision',
            'type'=>'integer',
            'subtype'=>'',
            'readonly'=>true
        ),
        'inv_uid' => array(
            'title'=>'Inv. Id',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>true
        ),
        'date_start' => array(
            'title'=>'Date Start',
            'type'=>'date',
            'subtype'=>'',
            'readonly'=>false
        ),
        'date_end' => array(
            'title'=>'Date Start',
            'type'=>'date',
            'subtype'=>'',
            'readonly'=>false
        ),
        'ts_updated' => array(
            'title'=>'Time Changed',
            'type'=>'timestamp',
            'subtype'=>'',
            'readonly'=>true,
            'default'=>array("value"=>"now", "on"=>"update")
        ),
        'ts_start' => array(
            'title'=>'Time Start',
            'type'=>'timestamp',
            'subtype'=>'',
            'readonly'=>false,
            'default'=>array("value"=>"now", "on"=>"null")
        ),
        'ts_end' => array(
            'title'=>'Time End',
            'type'=>'timestamp',
            'subtype'=>'',
            'readonly'=>false,
            'default'=>array("value"=>"now", "on"=>"null")
        ),
        'user_id' => array('title'=>'Owner',
            'type'=>'object',
            'subtype'=>'user',
            'default'=>array("value"=>"-3", "on"=>"null")
        ),
        'recur_id' => array(
            'title'=>'Recurrance',
            'type'=>'integer',
            'readonly'=>true
        ),
        'recurrence_pattern' => array(
            'title'=>'Recurrence',
            'readonly'=>true,
            'type'=>'integer'
        ),
        // This is deprecated, we should eventually just delete it
        'contact_id' => array(
            'title'=>'Contact',
            'type'=>'object',
            'subtype'=>'contact_personal'
        ),
        'calendar' => array(
            'title'=>'Calendar',
            'type'=>'fkey',
            'subtype'=>'calendars',
            'fkey_table'=>array("key"=>"id", "title"=>"name")
        ),
        'customer_id' => array(
            'title'=>'Customer',
            'type'=>'object',
            'subtype'=>'customer'
        ),
        'attendees' => array(
            'title'=>'Attendees',
            'type'=>'object_multi',
            'subtype'=>'member'
        ),
        'obj_reference' => array(
            'title'=>'Reference',
            'type'=>'object',
            'subtype'=>'',
            'readonly'=>true
        ),
    ),
    'recur_rules' => array(
        "field_time_start"=>"ts_start",
        "field_time_end"=>"ts_end",
        "field_date_start"=>"ts_start",
        "field_date_end"=>"ts_end",
        "field_recur_id"=>"recur_id",
    ),
    'default_activity_level' => 1,
    'is_private' => false,
);
