<?php

/**
 * Return browser views for entity of object type 'email_message'
 */

namespace data\browser_views;
use Netric\EntityDefinition\ObjectTypes;

return [
    'obj_type' => ObjectTypes::EMAIL_MESSAGE,
    'views' => [
        'email_messages' => [
            'name' => 'Messages',
            'description' => '',
            'default' => true,
            'filter_fields' => [],
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
