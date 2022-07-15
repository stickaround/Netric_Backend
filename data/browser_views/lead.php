<?php

/**
 * Return browser views for entity of object type 'lead'
 */

namespace data\browser_views;

use Netric\Entity\ObjType\UserEntity;
use Netric\EntityQuery\Where;
use Netric\EntityDefinition\ObjectTypes;

return [
    'obj_type' => ObjectTypes::LEAD,
    'views' => [
        'my_leads' => [
            'obj_type' => 'lead',
            'name' => 'My Leads',
            'description' => 'Leads Assigned To Me',
            'default' => true,
            'filter_fields' => [],
            'conditions' => [
                'owner' => [
                    'blogic' => Where::COMBINED_BY_AND,
                    'field_name' => 'owner_id',
                    'operator' => Where::OPERATOR_EQUAL_TO,
                    'value' => UserEntity::USER_CURRENT,
                ],
            ],
            'order_by' => [
                'sort_order' => [
                    'field_name' => 'sort_order',
                    'direction' => 'desc',
                ],
            ],
            'table_columns' => ['first_name', 'last_name', 'email', 'phone', 'city', 'district', 'status_id', 'rating_id', 'ts_entered']
        ],
    
        'all_leads' => [
            'obj_type' => 'lead',
            'name' => 'All Leads',
            'description' => 'All Leads',
            'default' => false,
            'filter_fields' => [],
            'conditions' => [
                'owner' => [
                    'blogic' => Where::COMBINED_BY_AND,
                    'field_name' => 'owner_id',
                    'operator' => Where::OPERATOR_EQUAL_TO,
                    'value' => UserEntity::USER_CURRENT,
                ],
            ],
            'order_by' => [
                'sort_order' => [
                    'field_name' => 'sort_order',
                    'direction' => 'desc',
                ],
            ],
            'table_columns' => ['first_name', 'last_name', 'email', 'phone', 'city', 'district', 'status_id', 'rating_id', 'ts_entered']
        ],
    ]
];
