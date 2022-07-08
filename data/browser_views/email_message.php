<?php

/**
 * Return browser views for entity of object type 'email_message'
 */

namespace data\browser_views;
use Netric\EntityDefinition\ObjectTypes;

return [
    "obj_type" => ObjectTypes::EMAIL_MESSAGE,
    "filters" => [],
    "views" => [
        'email_messages' => [
            'name' => 'Messages',
            'description' => '',
            'default' => true,
            'order_by' => [
                'date' => [
                    'field_name' => 'message_date',
                    'direction' => 'desc',
                ],
            ],
            'table_columns' => ['subject', 'message_date', 'from', 'to', 'priority']
        ],
    ]
];
