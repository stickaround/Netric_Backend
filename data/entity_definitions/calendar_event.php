<?php

namespace data\entity_definitions;

use Netric\EntityDefinition\Field;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\ObjectTypes;

return array(
    'fields' => array(
        'name' => array(
            'title' => 'Name',
            'type' => Field::TYPE_TEXT,
            'subtype' => '512',
            'readonly' => false
        ),
        'location' => array(
            'title' => 'Location',
            'type' => Field::TYPE_TEXT,
            'subtype' => '512',
            'readonly' => false
        ),
        'notes' => array(
            'title' => 'Notes',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => false
        ),
        'start_block' => array(
            'title' => 'Start Minute',
            'type' => Field::TYPE_INTEGER,
            'subtype' => '',
            'readonly' => true
        ),
        'end_block' => array(
            'title' => 'End Minute',
            'type' => Field::TYPE_INTEGER,
            'subtype' => '',
            'readonly' => true
        ),
        'all_day' => array(
            'title' => 'All Day',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => false
        ),
        'sharing' => array(
            'title' => 'Sharing',
            'type' => Field::TYPE_INTEGER,
            'subtype' => '',
            'optional_values' => array(
                "2" => "Public",
                "1" => "Private"
            ),
            'readonly' => false
        ),
        'inv_eid' => array(
            'title' => 'Invitation ID',
            'type' => Field::TYPE_INTEGER,
            'subtype' => '',
            'readonly' => true
        ),
        'inv_rev' => array(
            'title' => 'Inv. Revision',
            'type' => Field::TYPE_INTEGER,
            'subtype' => '',
            'readonly' => true
        ),
        'inv_uid' => array(
            'title' => 'Inv. Id',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => true
        ),
        'date_start' => array(
            'title' => 'Date Start',
            'type' => Field::TYPE_DATE,
            'subtype' => '',
            'readonly' => false
        ),
        'date_end' => array(
            'title' => 'Date Start',
            'type' => Field::TYPE_DATE,
            'subtype' => '',
            'readonly' => false
        ),
        'ts_updated' => array(
            'title' => 'Time Changed',
            'type' => Field::TYPE_TIMESTAMP,
            'subtype' => '',
            'readonly' => true,
            'default' => array("value" => "now", "on" => "update")
        ),
        'ts_start' => array(
            'title' => 'Time Start',
            'type' => Field::TYPE_TIMESTAMP,
            'subtype' => '',
            'readonly' => false,
            'default' => array("value" => "now", "on" => "null")
        ),
        'ts_end' => array(
            'title' => 'Time End',
            'type' => Field::TYPE_TIMESTAMP,
            'subtype' => '',
            'readonly' => false,
            'default' => array("value" => "now", "on" => "null")
        ),
        'recur_id' => array(
            'title' => 'Recurrance',
            'type' => Field::TYPE_TEXT,
            'subtype' => 36,
            'readonly' => true
        ),
        'recurrence_pattern' => array(
            'title' => 'Recurrence',
            'readonly' => true,
            'type' => Field::TYPE_INTEGER,
        ),
        // This is deprecated, we should eventually just delete it
        'contact_id' => array(
            'title' => 'Contact',
            'type' => Field::TYPE_OBJECT,
            'subtype' => 'contact_personal'
        ),
        'calendar' => array(
            'title' => 'Calendar',
            'type' => Field::TYPE_OBJECT,
            'subtype' => 'calendar',
            'fkey_table' => array("key" => "id", "title" => "name")
        ),
        'customer_id' => array(
            'title' => 'Customer',
            'type' => Field::TYPE_OBJECT,
            'subtype' => ObjectTypes::CONTACT,
        ),
        'attendees' => array(
            'title' => 'Attendees',
            'type' => Field::TYPE_OBJECT_MULTI,
            'subtype' => 'member'
        ),
        'obj_reference' => array(
            'title' => 'Reference',
            'type' => Field::TYPE_OBJECT,
            'subtype' => '',
            'readonly' => true
        ),
    ),
    'recur_rules' => array(
        "field_time_start" => "ts_start",
        "field_time_end" => "ts_end",
        "field_date_start" => "ts_start",
        "field_date_end" => "ts_end",
        "field_recur_id" => "recur_id",
    ),
    'default_activity_level' => 1,
    'is_private' => false,
);
