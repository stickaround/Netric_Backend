<?php
namespace data\entity_definitions;

return array(
    'fields' => array(
        'name' => array('title'=>'Name', 'type'=>'text', 'subtype'=>'512', 'readonly'=>false),
        'location' => array('title'=>'Location', 'type'=>'text', 'subtype'=>'512', 'readonly'=>false),
        'notes' => array('title'=>'Notes', 'type'=>'text', 'subtype'=>'', 'readonly'=>false),
        'f_closed' => array('title'=>'Closed/Converted', 'type'=>'bool', 'subtype'=>'', 'readonly'=>false),
        'user_id' => array('title'=>'Owner',
            'type'=>'object',
            'subtype'=>'user',
            'default'=>array("value"=>"-3", "on"=>"null")
        ),
        'event_id' => array(
            'title'=>'Event',
            'type'=>'fkey',
            'subtype'=>'calendar_events',
            'fkey_table'=>array("key"=>"id", "title"=>"name"),
            'readonly'=>true
        ),
        'attendees' => array(
            'title'=>'Attendees',
            'type'=>'object_multi',
            'subtype'=>'member'
        ),
        'status_id' => array(
            'title'=>'Status',
            'type'=>'fkey',
            'subtype'=>'object_groupings',
            'fkey_table'=>array("key"=>"id", "title"=>"name", "parent"=>"parent_id",
                "ref_table"=>array(
                    "table"=>"object_grouping_mem",
                    "this"=>"object_id",
                    "ref"=>"grouping_id"
                ),
            ),
        ),
    ),
    'is_private' => true,
    'default_activity_level' => 1,
);
