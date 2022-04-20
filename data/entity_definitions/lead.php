<?php

namespace data\entity_definitions;

use Netric\EntityDefinition\Field;
use Netric\EntityDefinition\ObjectTypes;

return [
    'list_title' => 'first_name',
    'fields' => [
        'name' => [
            'title' => 'Name',
            'type' => 'auto',
            'subtype' => '',
            'readonly' => true,
            'default' => [
                "value" => "Untitled",
                "on" => "null",
                "coalesce" => [
                    [
                        "first_name",
                        "last_name"
                    ],
                    "company"
                ],
            ],
        ],
        'first_name' => [
            'title' => 'First Name',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false
        ],
        'last_name' => [
            'title' => 'Last Name',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false
        ],
        'email' => [
            'title' => 'Email',
            'type' => Field::TYPE_TEXT,
            'subtype' => 'email',
            'readonly' => false
        ],
        'phone' => [
            'title' => 'Phone',
            'type' => Field::TYPE_TEXT,
            'subtype' => '64',
            'readonly' => false,
            'mask' => 'phone_dash'
        ],
        'phone2' => [
            'title' => 'Phone 2',
            'type' => Field::TYPE_TEXT,
            'subtype' => '64',
            'readonly' => false,
            'mask' => 'phone_dash'
        ],
        'phone3' => [
            'title' => 'Phone 3',
            'type' => Field::TYPE_TEXT,
            'subtype' => '64',
            'readonly' => false,
            'mask' => 'phone_dash'
        ],
        'fax' => [
            'title' => 'Fax',
            'type' => Field::TYPE_TEXT,
            'subtype' => '64',
            'readonly' => false
        ],
        'street' => [
            'title' => 'Street',
            'type' => Field::TYPE_TEXT,
            'subtype' => '128',
            'readonly' => false
        ],
        'street2' => [
            'title' => 'Street 2',
            'type' => Field::TYPE_TEXT,
            'subtype' => '128',
            'readonly' => false
        ],
        'city' => [
            'title' => 'City',
            'type' => Field::TYPE_TEXT,
            'subtype' => '128',
            'readonly' => false
        ],
        'district' => [
            'title' => 'State',
            'type' => Field::TYPE_TEXT,
            'subtype' => '64',
            'readonly' => false
        ],
        'postal_code' => [
            'title' => 'Zip',
            'type' => Field::TYPE_TEXT,
            'subtype' => 'zipcode',
            'readonly' => false
        ],
        'notes' => [
            'title' => 'Notes',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => false
        ],
        'company' => [
            'title' => 'Company',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false
        ],
        'title' => [
            'title' => 'Job Title',
            'type' => Field::TYPE_TEXT,
            'subtype' => '64',
            'readonly' => false
        ],
        'website' => [
            'title' => 'Website',
            'type' => Field::TYPE_TEXT,
            'subtype' => '128',
            'readonly' => false
        ],
        'country' => [
            'title' => 'Country',
            'type' => Field::TYPE_TEXT,
            'subtype' => '512',
            'readonly' => false
        ],
        'f_converted' => [
            'title' => 'Converted',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => true
        ],
        'is_seen' => [
            'title' => 'Seen',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => true,
            "default" => [
                "value" => 'f',
                "on" => "null"
            ],
        ],
        'campaign_id' => [
            'title' => 'Campaign',
            'type' => Field::TYPE_OBJECT,
            'subtype' => 'marketing_campaign'
        ],
        'queue_id' => [
            'title' => 'Queue',
            'type' => Field::TYPE_GROUPING,
            'subtype' => 'object_groupings',
        ],
        'source_id' => [
            'title' => 'Source',
            'type' => Field::TYPE_GROUPING,
            'subtype' => 'object_groupings',
        ],
        'rating_id' => [
            'title' => 'Rating',
            'type' => Field::TYPE_GROUPING,
            'subtype' => 'object_groupings',
        ],
        'status_id' => [
            'title' => 'Status',
            'type' => Field::TYPE_GROUPING,
            'subtype' => 'object_groupings',
        ],
        'class_id' => [
            'title' => 'Class',
            'type' => Field::TYPE_GROUPING,
            'subtype' => 'object_groupings',
        ],
        'converted_opportunity_id' => [
            'title' => 'Opportunity',
            'type' => Field::TYPE_OBJECT,
            'subtype' => 'opportunity'
        ],
        'converted_customer_id' => [
            'title' => 'Customer',
            'type' => Field::TYPE_OBJECT,
            'subtype' => ObjectTypes::CONTACT
        ],
        'ts_converted' => [
            'title' => 'Date Converted',
            'type' => Field::TYPE_TIMESTAMP,
            'subtype' => '',
            'readonly' => true
        ],
    ],
];
