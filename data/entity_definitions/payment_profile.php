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
            'title' => 'Contact',
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
        // Billing address for payment method (if needed)
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
        'address' => [
            'title' => 'Street',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false
        ],
        'address2' => [
            'title' => 'Street 2',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
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
            'subtype' => '128',
            'readonly' => false
        ],
        'postal_code' => [
            'title' => 'Zip',
            'type' => Field::TYPE_TEXT,
            'subtype' => 'zipcode',
            'readonly' => false
        ],
        // Default or preferred payment method
        'f_default' => [
            'title' => "Default",
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
        ],
    ],
];
