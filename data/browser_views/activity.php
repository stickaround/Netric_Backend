<?php

/**
 * Return browser views for entity of object type 'activity'
 */

namespace data\browser_views;

use Netric\EntityQuery\Where;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\ObjectTypes;

return [
    'obj_type' => ObjectTypes::ACTIVITY,    
    'views' => [
        'all_activity' => [        
            'name' => 'All Activity',
            'description' => '',
            'default' => true,
            'filter_fields' => ['type_id'],
            'conditions' => [
                'level' => [
                    'blogic' => Where::COMBINED_BY_AND,
                    'field_name' => 'level',
                    'operator' => Where::OPERATOR_GREATER_THAN_OR_EQUAL_TO,
                    'value' => 3
                ],
            ],
            'order_by' => [
                'sort_order' => [
                    'field_name' => 'sort_order',
                    'direction' => 'desc',
                ],
            ],
            'table_columns' => ['name', 'type_id', 'direction', 'ts_entered', 'user_id', 'notes', 'obj_reference']
        ],

        'my_team_activity' => [        
            'name' => 'My Team Activity',
            'description' => '',
            'default' => false,
            'filter_fields' => ['type_id'],
            'conditions' => [
                'level' => [
                    'blogic' => Where::COMBINED_BY_AND,
                    'field_name' => 'level',
                    'operator' => Where::OPERATOR_GREATER_THAN_OR_EQUAL_TO,
                    'value' => 3
                ],
                'team' => [
                    'blogic' => Where::COMBINED_BY_AND,
                    'field_name' => 'user_id.team_id',
                    'operator' => Where::OPERATOR_EQUAL_TO,
                    'value' => UserEntity::USER_CURRENT
                ],
            ],
            'order_by' => [
                'sort_order' => [
                    'field_name' => 'sort_order',
                    'direction' => 'desc',
                ],
            ],
            'table_columns' => ['name', 'type_id', 'direction', 'ts_entered', 'user_id', 'notes', 'obj_reference']
        ],

        'my_activity' => [        
            'name' => 'My Activity',
            'description' => '',
            'default' => false,
            'filter_fields' => ['type_id'],
            'conditions' => [
                'user' => [
                    'blogic' => Where::COMBINED_BY_AND,
                    'field_name' => 'user_id',
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
            'table_columns' => ['name', 'type_id', 'direction', 'ts_entered', 'user_id', 'notes', 'obj_reference']
        ],

        'tasks' => [        
            'name' => 'Tasks',
            'description' => '',
            'default' => false,
            'filter_fields' => ['type_id'],
            'conditions' => [
                'type' => [
                    'blogic' => Where::COMBINED_BY_AND,
                    'field_name' => 'type_id',
                    'operator' => Where::OPERATOR_EQUAL_TO,
                    'value' => 'Task'
                ],
            ],
            'order_by' => [
                'sort_order' => [
                    'field_name' => 'sort_order',
                    'direction' => 'desc',
                ],
            ],
            'table_columns' => ['name', 'type_id', 'direction', 'ts_entered', 'user_id', 'notes', 'obj_reference']
        ],

        'comments' => [        
            'name' => 'Commments',
            'description' => '',
            'default' => false,
            'filter_fields' => ['type_id'],
            'conditions' => [
                'type' => [
                    'blogic' => Where::COMBINED_BY_AND,
                    'field_name' => 'type_id',
                    'operator' => Where::OPERATOR_EQUAL_TO,
                    'value' => 'Comment'
                ],
            ],
            'order_by' => [
                'sort_order' => [
                    'field_name' => 'sort_order',
                    'direction' => 'desc',
                ],
            ],
            'table_columns' => ['name', 'type_id', 'direction', 'ts_entered', 'user_id', 'notes', 'obj_reference']
        ],
    
        'wall_posts' => [
            
            'name' => 'Wall Posts',
            'description' => '',
            'default' => false,
            'filter_fields' => ['type_id'],
            'conditions' => [
                'type' => [
                    'blogic' => Where::COMBINED_BY_AND,
                    'field_name' => 'type_id',
                    'operator' => Where::OPERATOR_EQUAL_TO,
                    'value' => 'Wall Post'
                ],
            ],
            'order_by' => [
                'sort_order' => [
                    'field_name' => 'sort_order',
                    'direction' => 'desc',
                ],
            ],
            'table_columns' => ['name', 'type_id', 'direction', 'ts_entered', 'user_id', 'notes', 'obj_reference']
        ],
    
        'phone_calls' => [
            
            'name' => 'Phone Calls',
            'description' => '',
            'default' => false,
            'filter_fields' => ['type_id'],
            'conditions' => [
                'type' => [
                    'blogic' => Where::COMBINED_BY_AND,
                    'field_name' => 'type_id',
                    'operator' => Where::OPERATOR_EQUAL_TO,
                    'value' => 'Phone Call'
                ],
            ],
            'order_by' => [
                'sort_order' => [
                    'field_name' => 'sort_order',
                    'direction' => 'desc',
                ],
            ],
            'table_columns' => ['name', 'type_id', 'direction', 'ts_entered', 'user_id', 'notes', 'obj_reference']
        ],

        'calendar_events' => [        
            'name' => 'Calendar Events',
            'description' => '',
            'default' => false,
            'filter_fields' => [],
            'conditions' => [
                'type' => [
                    'blogic' => Where::COMBINED_BY_AND,
                    'field_name' => 'type_id',
                    'operator' => Where::OPERATOR_EQUAL_TO,
                    'value' => 'Event'
                ],
            ],
            'order_by' => [
                'sort_order' => [
                    'field_name' => 'sort_order',
                    'direction' => 'desc',
                ],
            ],
            'table_columns' => ['name', 'type_id', 'direction', 'ts_entered', 'user_id', 'notes', 'obj_reference']
        ],
        
        'email' => [        
            'name' => 'Email',
            'description' => '',
            'default' => false,
            'filter_fields' => ['type_id'],
            'conditions' => [
                'type' => [
                    'blogic' => Where::COMBINED_BY_AND,
                    'field_name' => 'type_id',
                    'operator' => Where::OPERATOR_EQUAL_TO,
                    'value' => 'Email'
                ],
            ],
            'order_by' => [
                'sort_order' => [
                    'field_name' => 'sort_order',
                    'direction' => 'desc',
                ],
            ],
            'table_columns' => ['name', 'type_id', 'direction', 'ts_entered', 'user_id', 'notes', 'obj_reference']
        ],
    ]
];
