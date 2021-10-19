<?php

namespace data\entity_definitions;

use Netric\EntityDefinition\Field;
use Netric\EntityDefinition\ObjectTypes;

return [
    'fields' => [
        'amount' => [
            'title' => 'Amount',
            'type' => Field::TYPE_NUMBER,
            'subtype' => 'double precision',
            'readonly' => false
        ],
        'date_paid' => [
            'title' => 'Date Paid',
            'type' => Field::TYPE_DATE,
            'subtype' => '',
            'readonly' => false
        ],
        'ref' => [
            'title' => 'Ref / Check Number',
            'type' => Field::TYPE_TEXT,
            'subtype' => '512',
            'readonly' => false
        ],
        'payment_method' => [
            'title' => 'Payment Method',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false
        ],
        'customer_id' => [
            'title' => 'Customer',
            'type' => Field::TYPE_OBJECT,
            'subtype' => ObjectTypes::CONTACT,
        ],
        'invoice_id' => [
            'title' => 'Invoice',
            'type' => Field::TYPE_OBJECT,
            'subtype' => 'invoice'
        ],
        'order_id' => [
            'title' => 'Order',
            'type' => Field::TYPE_OBJECT,
            'subtype' => 'order'
        ],
    ],
];
