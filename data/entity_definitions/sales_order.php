<?php

namespace data\entity_definitions;

use Netric\EntityDefinition\Field;
use Netric\EntityDefinition\ObjectTypes;

return [
    'fields' => [
        'name' => [
            'title' => 'Name',
            'type' => Field::TYPE_TEXT,
            'subtype' => '512',
            'readonly' => false
        ],
        'created_by' => [
            'title' => 'Created By',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => true
        ],
        'tax_rate' => [
            'title' => 'Tax %',
            'type' => Field::TYPE_INTEGER,
            'subtype' => '',
            'readonly' => false
        ],
        'amount' => [
            'title' => 'Amount',
            'type' => Field::TYPE_NUMBER,
            'subtype' => 'double precision',
            'readonly' => false
        ],
        'ship_to' => [
            'title' => 'Ship To',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => false
        ],
        'ship_to_cship' => [
            'title' => 'Use Billing Address',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => false,
            'default' => ["value" => "t", "on" => "null"]
        ],
        'status_id' => [
            'title' => 'Status',
            'type' => Field::TYPE_GROUPING,
            'subtype' => 'object_groupings',
        ],
        'customer_id' => [
            'title' => 'Customer',
            'type' => Field::TYPE_OBJECT,
            'subtype' => ObjectTypes::CONTACT
        ],
        'invoice_id' => [
            'title' => 'Invoice',
            'type' => Field::TYPE_OBJECT,
            'subtype' => 'invoice'
        ],
        'sales_order_id' => [
            'title' => 'Sales Order Id',
            'type' => Field::TYPE_INTEGER,
        ],
    ],
];
