<?php

/**
 * Return browser views for entity of object type 'email_thread'
 */

namespace data\browser_views;

use Netric\EntityQuery\Where;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\ObjectTypes;

return [
    "obj_type" => ObjectTypes::EMAIL_THREAD,
    "filters" => [],
    "views" => [
        'email_threads' => [
            'name' => 'Email Threads',
            'description' => '',
            'default' => true,
            'conditions' => [
                'user' => [
                    'blogic' => Where::COMBINED_BY_AND,
                    'field_name' => 'owner_id',
                    'operator' => Where::OPERATOR_EQUAL_TO,
                    'value' => UserEntity::USER_CURRENT,
                ],
            ],
            'order_by' => [
                'date' => [
                    'field_name' => 'ts_delivered',
                    'direction' => 'desc',
                ],
            ],
            'table_columns' => ['senders', 'subject', 'ts_delivered', 'is_seen', 'f_flagged', 'num_attachments', 'num_messages']
        ],
    ]
];
