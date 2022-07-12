<?php

/**
 * Return browser views for entity of object type 'user'
 */

namespace data\browser_views;

use Netric\Entity\ObjType\UserEntity;
use Netric\EntityQuery\Where;
use Netric\EntityDefinition\ObjectTypes;

return [
    'obj_type' => ObjectTypes::USER,
    'views' => [
        'active' => [        
            'name' => 'Active',
            'description' => 'Active Users',
            'default' => true,
            'filter_fields' => ['team_id', 'manager_id'],
            'conditions' => [
                'active' => [
                    'blogic' => Where::COMBINED_BY_AND,
                    'field_name' => 'active',
                    'operator' => Where::OPERATOR_EQUAL_TO,
                    'value' => true
                ],
                'internal' => [
                    'blogic' => Where::COMBINED_BY_AND,
                    'field_name' => 'type',
                    'operator' => Where::OPERATOR_EQUAL_TO,
                    'value' => UserEntity::TYPE_INTERNAL
                ],
            ],
            'order_by' => [
                'name' => [
                    'field_name' => 'full_name',
                    'direction' => 'asc',
                ],
            ],
            'table_columns' => ['full_name', 'name', 'last_login', 'team_id', 'manager_id']
        ],
    
        'inactive_users' => [        
            'name' => 'Inactive Users',
            'description' => 'Inactive Users',
            'filter_fields' => ['team_id', 'manager_id'],
            'default' => false,
            'conditions' => [
                'active' => [
                    'blogic' => Where::COMBINED_BY_AND,
                    'field_name' => 'active',
                    'operator' => Where::OPERATOR_EQUAL_TO,
                    'value' => false
                ],
                'internal' => [
                    'blogic' => Where::COMBINED_BY_AND,
                    'field_name' => 'type',
                    'operator' => Where::OPERATOR_EQUAL_TO,
                    'value' => UserEntity::TYPE_INTERNAL
                ],
            ],
            'order_by' => [
                'name' => [
                    'field_name' => 'full_name',
                    'direction' => 'asc',
                ],
            ],
            'table_columns' => ['full_name', 'name', 'last_login', 'team_id', 'manager_id']
        ],
    
        'all_users' => [        
            'name' => 'All Users',
            'description' => 'All Users',
            'filter_fields' => ['team_id', 'manager_id', 'active'],
            'default' => false,
            'order_by' => [
                'name' => [
                    'field_name' => 'full_name',
                    'direction' => 'asc',
                ],
            ],
            'table_columns' => ['full_name', 'name', 'last_login', 'team_id', 'manager_id']
        ],
    ]
];
