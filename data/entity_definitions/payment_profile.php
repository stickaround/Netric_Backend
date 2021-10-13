<?php

namespace data\entity_definitions;

use Netric\EntityDefinition\Field;
use Netric\EntityDefinition\ObjectTypes;

return [
    'store_revisions' => true,
    'icon' => 'folder',
    'fields' => [
        'name' => [
            'title' => 'Name',
            'type' => Field::TYPE_TEXT,
            'subtype' => 64,
        ],
        'customer' => [
            'title' => 'Customer',
            'type' => Field::TYPE_OBJECT,
            'subtype' => ObjectTypes::CONTACT
        ],
        'payment_gateway' => [
            'title' => 'Payment Gateway',
            'type' => Field::TYPE_TEXT,
            'subtype' => 64,
            'readonly' => true,
        ],
        'token' => [
            'title' => 'Payment Method Token',
            'type' => Field::TYPE_TEXT,
        ],
        // Default or preferred payment method
        'f_default' => [
            'title' => "Default",
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
        ],
    ],
];
