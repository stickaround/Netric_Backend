<?php
namespace data\entity_definitions;
use Netric\EntityDefinition\Field;

return [
    'store_revisions' => true,
    'icon' => 'folder',
    'fields' => [
        'contact_id' => [
            'title'=>'Contact',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'customer'
        ],
        'payment_gateway' => [
            'title'=>'Payment Gateway',
            'type'=>Field::TYPE_TEXT,
            'subtype' => 64,
            'readonly' => true,
        ],
        'gateway_customer_id' => [
            'title'=>'Gateway Customer ID',
            'type'=>Field::TYPE_TEXT,
            'subtype' => 64,
        ],
        'gateway_payment_id' => [
            'title'=>'Gateway Payment ID',
            'type'=>Field::TYPE_TEXT,
            'subtype' => 64,
        ],
        'f_default' => [
            'title'=>"Default",
            'type'=>Field::TYPE_BOOL,
            'subtype'=>'',
        ],
    ],
];
