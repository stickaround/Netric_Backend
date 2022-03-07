<?php

namespace data\entity_definitions;

use Netric\EntityDefinition\Field;
use Netric\EntityDefinition\ObjectTypes;

return [
    'is_private' => false,
    'default_activity_level' => 1,
    'store_revisions' => true,
    'fields' => [
        'name' => [
            'title' => 'Name',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => false,
            'required' => true,
        ],

        'notes' => [
            'title' => 'Notes',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => false
        ],

        'closed_lost_reason' => [
            'title' => 'Closed Lost Reason',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => false
        ],

        'expected_close_date' => [
            'title' => 'Exp. Close Date',
            'type' => Field::TYPE_DATE,
            'subtype' => '',
            'readonly' => false
        ],

        'amount' => [
            'title' => 'Est. Amount',
            'type' => Field::TYPE_NUMBER,
            'subtype' => 'double precision',
            'readonly' => false,
        ],

        'ts_closed' => [
            'title' => 'Time Closed',
            'type' => Field::TYPE_TIMESTAMP,
            'subtype' => '',
            'readonly' => false
        ],

        'probability_per' => [
            'title' => 'Est. Probability %',
            'type' => Field::TYPE_NUMBER,
            'subtype' => '',
            'readonly' => false,
            'optional_values' => [
                "10" => "10%",
                "25" => "25%",
                "50" => "50%",
                "75" => "75%",
                "90" => "90%",
                "100" => "100%"
            ],
        ],

        'f_closed' => [
            'title' => 'Closed',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => false,
            'default' => ["value" => "f", "on" => "null"],
        ],

        'f_won' => [
            'title' => 'Won',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => false,
            'default' => ["value" => "f", "on" => "null"],
        ],

        // Marketing campaign references
        'campaign_id' => [
            'title' => 'Campaign',
            'type' => Field::TYPE_OBJECT,
            'subtype' => 'marketing_campaign'
        ],

        'stage_id' => [
            'title' => 'Stage',
            'type' => Field::TYPE_GROUPING,
            'subtype' => 'object_groupings',
        ],

        'customer_id' => [
            'title' => 'Contact',
            'type' => Field::TYPE_OBJECT,
            'subtype' => ObjectTypes::CONTACT,
            'required' => true
        ],

        'lead_id' => [
            'title' => 'Lead',
            'type' => Field::TYPE_OBJECT,
            'subtype' => 'lead'
        ],

        'lead_source_id' => [
            'title' => 'Source',
            'type' => Field::TYPE_GROUPING,
            'subtype' => 'object_groupings',
        ],
        'type_id' => [
            'title' => 'Type',
            'type' => Field::TYPE_GROUPING,
            'subtype' => 'object_groupings',
        ],
        'objection_id' => [
            'title' => 'Objection',
            'type' => Field::TYPE_GROUPING,
            'subtype' => 'object_groupings',
        ],
        'selling_point_id' => [
            'title' => 'Selling Point',
            'type' => Field::TYPE_GROUPING,
            'subtype' => 'object_groupings',
        ],
    ],
];
