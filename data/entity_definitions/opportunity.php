<?php

namespace data\entity_definitions;

use Netric\EntityDefinition\Field;
use Netric\EntityDefinition\ObjectTypes;

return array(
    'is_private' => false,
    'default_activity_level' => 1,
    'store_revisions' => false,
    'fields' => array(
        'name' => array(
            'title' => 'Name',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => false,
            'required' => true,
        ),

        'notes' => array(
            'title' => 'Notes',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => false
        ),

        'closed_lost_reason' => array(
            'title' => 'Closed Lost Reason',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => false
        ),

        'expected_close_date' => array(
            'title' => 'Exp. Close Date',
            'type' => Field::TYPE_DATE,
            'subtype' => '',
            'readonly' => false
        ),

        'amount' => array(
            'title' => 'Est. Amount',
            'type' => Field::TYPE_NUMBER,
            'subtype' => 'double precision',
            'readonly' => false,
        ),

        'ts_closed' => array(
            'title' => 'Time Closed',
            'type' => Field::TYPE_TIMESTAMP,
            'subtype' => '',
            'readonly' => false
        ),

        'probability_per' => array(
            'title' => 'Est. Probability %',
            'type' => Field::TYPE_INTEGER,
            'subtype' => '',
            'readonly' => false,
            'optional_values' => array(
                "10" => "10%", "25" => "25%", "50" => "50%", "75" => "75%", "90" => "90%", "100" => "100%"
            ),
        ),

        'f_closed' => array(
            'title' => 'Closed',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => false,
            'default' => array("value" => "f", "on" => "null"),
        ),

        'f_won' => array(
            'title' => 'Won',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => false,
            'default' => array("value" => "f", "on" => "null"),
        ),

        // Marketing campaign references
        'campaign_id' => array(
            'title' => 'Campaign',
            'type' => Field::TYPE_OBJECT,
            'subtype' => 'marketing_campaign'
        ),

        'stage_id' => array(
            'title' => 'Stage',
            'type' => Field::TYPE_GROUPING,
            'subtype' => 'object_groupings',
        ),

        'customer_id' => array(
            'title' => 'Contact',
            'type' => Field::TYPE_OBJECT,
            'subtype' => ObjectTypes::CONTACT,
            'required' => true
        ),

        'lead_id' => array(
            'title' => 'Lead',
            'type' => Field::TYPE_OBJECT,
            'subtype' => 'lead'
        ),

        'lead_source_id' => array(
            'title' => 'Source',
            'type' => Field::TYPE_GROUPING,
            'subtype' => 'object_groupings',
        ),
        'type_id' => array(
            'title' => 'Type',
            'type' => Field::TYPE_GROUPING,
            'subtype' => 'object_groupings',
        ),
        'objection_id' => array(
            'title' => 'Objection',
            'type' => Field::TYPE_GROUPING,
            'subtype' => 'object_groupings',
        ),
        'selling_point_id' => array(
            'title' => 'Selling Point',
            'type' => Field::TYPE_GROUPING,
            'subtype' => 'object_groupings',
        ),
    ),
);
