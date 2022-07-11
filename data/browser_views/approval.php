<?php
/**
 * Return browser views for entity of object type 'approval'
 */

namespace data\browser_views;

use Netric\EntityQuery\Where;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\ObjectTypes;

return [
    'obj_type' => ObjectTypes::APPROVAL,
    'views' => [
        'awaiting_my_approval' => [        
            'name' => 'Awaiting My Approval',
            'description' => '',
            'default' => false,
            'filter_fields' => [],
            'conditions' => [
                'owner' => [
                    'blogic' => Where::COMBINED_BY_AND,
                    'field_name' => 'owner_id',
                    'operator' => Where::OPERATOR_EQUAL_TO,
                    'value' => UserEntity::USER_CURRENT,
                ],
                'status' => [
                    'blogic' => Where::COMBINED_BY_AND,
                    'field_name' => 'status',
                    'operator' => Where::OPERATOR_EQUAL_TO,
                    'value' => 'awaiting'
                ],
            ],
            'order_by' => [
                'sort_order' => [
                    'field_name' => 'sort_order',
                    'direction' => 'desc',
                ],
            ],
            'table_columns' => ['name', 'status', 'requested_by', 'owner_id', 'ts_entered']
        ],
        
        'all_approval_request' => [        
            'name' => 'All Approval Requests',
            'description' => '',
            'default' => false,
            'filter_fields' => [],
            'order_by' => [
                'sort_order' => [
                    'field_name' => 'sort_order',
                    'direction' => 'desc',
                ],
            ],
            'table_columns' => ['name', 'status', 'requested_by', 'owner_id', 'ts_entered']
        ],
    
        'my_approved' => [        
            'name' => 'My Approved',
            'description' => '',
            'default' => true,
            'filter_fields' => [],
            'conditions' => [
                'user' => [
                    'blogic' => Where::COMBINED_BY_AND,
                    'field_name' => 'user_id',
                    'operator' => Where::OPERATOR_EQUAL_TO,
                    'value' => UserEntity::USER_CURRENT,
                ],
                'status' => [
                    'blogic' => Where::COMBINED_BY_AND,
                    'field_name' => 'status',
                    'operator' => Where::OPERATOR_EQUAL_TO,
                    'value' => 'approved'
                ],
            ],
            'order_by' => [
                'sort_order' => [
                    'field_name' => 'sort_order',
                    'direction' => 'desc',
                ],
            ],
            'table_columns' => ['name', 'status', 'requested_by', 'owner_id', 'ts_entered']
        ],
    ]
];
