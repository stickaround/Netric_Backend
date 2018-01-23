<?php
namespace data\entity_definitions;
use Netric\EntityDefinition\Field;

return array(
    'fields' => array(
        // Textual name or subject
        'name' => array(
            'title'=>'Subject',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>false,
        ),

        // The object we are reminding on
        'obj_reference' => array(
            'title'=>'Concering',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'',
            'readonly'=>false,
        ),

        // The interval to execute
        'interval' => array(
            'title'=>'Interval',
            'type'=>Field::TYPE_NUMBER,
            'subtype'=>'integer',
            'readonly'=>false,
        ),

        // The units to use with interval (mintes|hours|days|weeks|months|years)
        'interval_unit' => array(
            'title'=>'Interval Unit',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'32',
            'readonly'=>false,
            'optional_values'=>array(
                "minutes" => "Minute(s)",
                "hours" => "Hour(s)",
                "days" => "Day(s)",
                "weeks" => "Week(s)",
                "months" => "Month(s)",
                "years" => "Year(s)",
            ),
        ),

        // The timestamp or data field to use to calculate ts_execute against
        'field_name' => array(
            'title'=>'Field Name',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>true,
        ),

        // The actual time when this reminder should execute
        'ts_execute' => array(
            'title'=>'Execute Time',
            'type'=>Field::TYPE_TIMESTAMP,
            'subtype'=>'',
            'readonly'=>false,
        ),

        // The owner of this reminder
        'owner_id' => array(
            'title'=>'Owner',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'user',
            'readonly'=>false,
            'default'=>array(
                "on"=>"null",
                "value"=>"-3",
            ),
        ),

        // Flag indicating the reminder was executed
        'f_executed' => array(
            'title'=>'Completed',
            'type'=>Field::TYPE_BOOL,
            'subtype'=>'',
            'readonly'=>true,
        ),

        // Send to variable
        'send_to' => array(
            'title'=>'Send To',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>true,
        ),

        // Notes for reminder if manual
        'notes' => array(
            'title'=>'Notes',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>false,
        ),

        // The timestamp or data field to use to calculate ts_execute against
        'action_type' => array(
            'title'=>'Type',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'32',
            'readonly'=>false,
            'optional_values'=>array(
                "popup" => "Pop-up",
                "email" => "Email",
                "sms" => "SMS",
            ),
        ),
    ),
);
