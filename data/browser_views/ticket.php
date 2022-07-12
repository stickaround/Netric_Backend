<?php

/**
 * Return browser views for entity of object type 'ticket'
 */

namespace data\browser_views;

use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\ObjectTypes;
use Netric\EntityQuery\Where;

return [
    'obj_type' => ObjectTypes::TICKET,
    'views' => [
        'default' => [        
            'name' => 'All Tickets',
            'description' => 'All tickets',
            'default' => true,
            'conditions' => [],
            'group_first_order_by' => true,
            'filter_fields' => ['channel_id', 'status_id', 'souce_id', 'owner_id', 'is_closed', 'is_seen'],
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
            'name' => 'My Tickets',
            'description' => 'Open tickets assigned to me',
            'default' => false,
            'filter_fields' => ['channel_id', 'status_id', 'souce_id', 'is_seen'],
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
            'name' => 'New/Unseen Tickets',
            'description' => 'Tickets that have been unseen or are unassigned',
            'default' => false,
            'filter_fields' => ['channel_id', 'status_id', 'souce_id'],
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
            'order_by' => [
                'date' => [
                    'field_name' => 'ts_updated',
                    'direction' => 'asc',
                ],
            ],
            'table_columns' => ['name', 'channel_id', 'status_id', 'souce_id', 'owner_id']
        ],
        'unassigned_tickets' => [        
            'name' => 'Unassigned Tickets',
            'description' => 'Tickets that are unassigned',
            'default' => false,
            'filter_fields' => ['channel_id', 'status_id', 'souce_id', 'is_seen'],
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
            'order_by' => [
                'date' => [
                    'field_name' => 'ts_updated',
                    'direction' => 'asc',
                ],
            ],
            'table_columns' => ['name', 'channel_id', 'status_id', 'souce_id']
        ],
        'all_tickets' => [        
            'name' => 'All Tickets',
            'description' => 'All tickets',
            'default' => false,
            'conditions' => [],
            'group_first_order_by' => true,
            'filter_fields' => ['channel_id', 'status_id', 'souce_id', 'owner_id', 'is_closed', 'is_seen'],
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
    ]
];
