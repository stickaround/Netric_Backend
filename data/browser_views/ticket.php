<?php

/**
 * Return browser views for entity of object type 'note'
 */

namespace data\browser_views;

use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\ObjectTypes;
use Netric\EntityQuery\Where;

return [
    'default' => [
        'obj_type' => ObjectTypes::TICKET,
        'name' => 'All Tickets',
        'description' => 'All tickets',
        'default' => true,
        'conditions' => [],
        'filter_key' => 'channel_id',
        'group_first_order_by' => true,
        'order_by' => [
            'status_id' => [
                'field_name' => 'status_id',
                'direction' => 'asc',
            ],
            'date' => [
                'field_name' => 'ts_updated',
                'direction' => 'asc',
            ],
        ],
        'table_columns' => ['name', 'channel_id', 'status_id', 'souce_id']
    ],
    'my_tickets' => [
        'obj_type' => ObjectTypes::TICKET,
        'name' => 'My Tickets',
        'description' => 'Open tickets assigned to me',
        'default' => false,
        'conditions' => [
            'user' => [
                'blogic' => Where::COMBINED_BY_AND,
                'field_name' => 'owner_id',
                'operator' => Where::OPERATOR_EQUAL_TO,
                'value' => UserEntity::USER_CURRENT,
            ],
            'not_closed' => [
                'blogic' => Where::COMBINED_BY_AND,
                'field_name' => 'is_closed',
                'operator' => Where::OPERATOR_NOT_EQUAL_TO,
                'value' => true
            ]
        ],
        'filter_key' => 'channel_id',
        'group_first_order_by' => true,
        'order_by' => [
            'status_id' => [
                'field_name' => 'status_id',
                'direction' => 'asc',
            ],
            'date' => [
                'field_name' => 'ts_updated',
                'direction' => 'asc',
            ],
        ],
        'table_columns' => ['name', 'channel_id', 'status_id', 'souce_id']
    ],
    'unseen_tickets' => [
        'obj_type' => ObjectTypes::TICKET,
        'name' => 'New/Unseen Tickets',
        'description' => 'Tickets that have been unseen or are unassigned',
        'default' => false,
        'conditions' => [
            'not_closed' => [
                'blogic' => Where::COMBINED_BY_AND,
                'field_name' => 'is_closed',
                'operator' => Where::OPERATOR_NOT_EQUAL_TO,
                'value' => true
            ],
            'is_seen' => [
                'blogic' => Where::COMBINED_BY_AND,
                'field_name' => 'is_seen',
                'operator' => Where::OPERATOR_EQUAL_TO,
                'value' => false,
            ],
            'owner_id' => [
                'blogic' => Where::COMBINED_BY_OR,
                'field_name' => 'owner_id',
                'operator' => Where::OPERATOR_EQUAL_TO,
                'value' => '',
            ],
        ],
        'filter_key' => 'channel_id',
        'order_by' => [
            'date' => [
                'field_name' => 'ts_updated',
                'direction' => 'asc',
            ],
        ],
        'table_columns' => ['name', 'channel_id', 'status_id', 'souce_id', "owner_id"]
    ],
    'unassigned_tickets' => [
        'obj_type' => ObjectTypes::TICKET,
        'name' => 'Unassigned Tickets',
        'description' => 'Tickets that are unassigned',
        'default' => false,
        'conditions' => [
            'not_closed' => [
                'blogic' => Where::COMBINED_BY_AND,
                'field_name' => 'is_closed',
                'operator' => Where::OPERATOR_NOT_EQUAL_TO,
                'value' => true
            ],
            'owner_id' => [
                'blogic' => Where::COMBINED_BY_AND,
                'field_name' => 'owner_id',
                'operator' => Where::OPERATOR_EQUAL_TO,
                'value' => '',
            ],
        ],
        'filter_key' => 'channel_id',
        'order_by' => [
            'date' => [
                'field_name' => 'ts_updated',
                'direction' => 'asc',
            ],
        ],
        'table_columns' => ['name', 'channel_id', 'status_id', 'souce_id']
    ],
    'all_tickets' => [
        'obj_type' => ObjectTypes::TICKET,
        'name' => 'All Tickets',
        'description' => 'All tickets',
        'default' => false,
        'conditions' => [],
        'filter_key' => 'channel_id',
        'group_first_order_by' => true,
        'order_by' => [
            'status_id' => [
                'field_name' => 'status_id',
                'direction' => 'asc',
            ],
            'date' => [
                'field_name' => 'ts_updated',
                'direction' => 'asc',
            ],
        ],
        'table_columns' => ['name', 'channel_id', 'status_id', 'souce_id']
    ],
];
