<?php
namespace data\entity_definitions;

use Netric\EntityDefinition\Field;
use Netric\Entity\ObjType\UserEntity;

return array(
    'fields' => array(
        'name' => array('title'=>'Name', 'type'=>'text', 'subtype'=>'512', 'readonly'=>false),
        'location' => array('title'=>'Location', 'type'=>'text', 'subtype'=>'512', 'readonly'=>false),
        'notes' => array('title'=>'Description', 'type'=>'text', 'subtype'=>'', 'readonly'=>false),
        'f_closed' => array('title'=>'Closed/Converted', 'type'=>'bool', 'subtype'=>'', 'readonly'=>false),
        'user_id' => array('title'=>'Owner',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'user',
            'default'=>array("value"=>UserEntity::USER_CURRENT, "on"=>"null")
        ),
        'event_id' => array(
            'title'=>'Event',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'calendar_event',
            'readonly'=>true
        ),
        'attendees' => array(
            'title'=>'Attendees',
            'type'=>Field::TYPE_OBJECT_MULTI,
            'subtype'=>'member'
        ),
        'status_id' => array(
            'title'=>'Status',
            'type'=>Field::TYPE_GROUPING,
            'subtype'=>'object_groupings',
        ),
    ),
    'is_private' => true,
    'default_activity_level' => 1,
);
