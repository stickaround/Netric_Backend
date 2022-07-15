<?php
/**
 * Return browser views for entity of object type 'opportunity'
 */

namespace data\browser_views;

use Netric\Entity\ObjType\UserEntity;
use Netric\EntityQuery\Where;
use Netric\EntityDefinition\ObjectTypes;

return [
    'obj_type' => ObjectTypes::OPPORTUNITY,
    'views' => [
        'my_open_opportunities' => [        
            'name' => 'My Open Opportunities',
            'description' => 'Opportunities assigned to me that are not closed',
            'default' => true,
            'filter_fields' => ['customer_id', 'stage_id', 'type_id'],
            'conditions' => [
                'owner' => [
                    'blogic' => Where::COMBINED_BY_AND,
                    'field_name' => 'owner_id',
                    'operator' => Where::OPERATOR_EQUAL_TO,
                    'value' => UserEntity::USER_CURRENT,
                ],
                'closed' => [
                    'blogic' => Where::COMBINED_BY_AND,
                    'field_name' => 'f_closed',
                    'operator' => Where::OPERATOR_NOT_EQUAL_TO,
                    'value' => 't'
                ],
            ],
            'order_by' => [
                'sort_order' => [
                    'field_name' => 'sort_order',
                    'direction' => 'desc',
                ],
            ],
            'table_columns' => ['name', 'customer_id', 'stage_id', 'amount', 'expected_close_date', 'probability_per', 'type_id']
        ],
    
        'all_my_opportunities' => [        
            'name' => 'All My Opportunities',
            'description' => 'Opportunities Assigned To Me',
            'default' => false,
            'filter_fields' => ['customer_id', 'stage_id', 'type_id'],
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
            'table_columns' => ['name', 'customer_id', 'stage_id', 'amount', 'expected_close_date', 'probability_per', 'owner_id']
        ],
    
        'all_open_opportunities' => [        
            'name' => 'All Open Opportunities',
            'description' => 'Opportunities Assigned To Me',
            'default' => false,
            'filter_fields' => ['customer_id', 'stage_id', 'type_id', 'owner_id'],
            'conditions' => [
                'closed' => [
                    'blogic' => Where::COMBINED_BY_AND,
                    'field_name' => 'f_closed',
                    'operator' => Where::OPERATOR_NOT_EQUAL_TO,
                    'value' => 't'
                ],
            ],
            'order_by' => [
                'sort_order' => [
                    'field_name' => 'sort_order',
                    'direction' => 'desc',
                ],
            ],
            'table_columns' => ['name', 'customer_id', 'stage_id', 'amount', 'expected_close_date', 'probability_per', 'owner_id']
        ],
    
        'all_opportunities' => [        
            'name' => 'All Opportunities',
            'description' => 'All Opportunities',
            'filter_fields' => ['customer_id', 'stage_id', 'type_id', 'owner_id', 'closed'],
            'default' => false,
            'order_by' => [
                'sort_order' => [
                    'field_name' => 'sort_order',
                    'direction' => 'desc',
                ],
            ],
            'table_columns' => ['name', 'customer_id', 'stage_id', 'amount', 'expected_close_date', 'probability_per', 'owner_id']
        ],
    ]
];
