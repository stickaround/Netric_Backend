<?php
/**
 * Return browser views for entity of object type 'time'
 */

namespace data\browser_views;

use Netric\Entity\ObjType\UserEntity;
use Netric\EntityQuery\Where;
use Netric\EntityDefinition\ObjectTypes;

return [
    'obj_type' => ObjectTypes::TIME,
    'views' => [
        'my_time' => [        
            'name' => 'My Time',
            'description' => '',
            'default' => true,
            'filter_fields' => ['task_id'],
            'order_by' => [
                'date' => [
                    'field_name' => 'date_applied',
                    'direction' => 'desc',
                ],
            ],
            'table_columns' => ['date_applied', 'hours', 'name', 'task_id']
        ],
    
        'my_teams_time' => [        
            'name' => 'My Team\'s Time',
            'description' => '',
            'default' => false,
            'filter_fields' => ['task_id', 'owner_id'],
            'conditions' => [
                'team' => [
                    'blogic' => Where::COMBINED_BY_AND,
                    'field_name' => 'owner_id.team_id',
                    'operator' => Where::OPERATOR_EQUAL_TO,
                    'value' => UserEntity::USER_CURRENT,
                ],
            ],
            'order_by' => [
                'date' => [
                    'field_name' => 'date_applied',
                    'direction' => 'desc',
                ],
            ],
            'table_columns' => ['date_applied', 'owner_id', 'hours', 'name', 'task_id']
        ],
    
        'all_time' => [        
            'name' => 'All Time',
            'description' => '',
            'default' => false,
            'filter_fields' => ['task_id', 'owner_id'],
            'order_by' => [
                'date' => [
                    'field_name' => 'date_applied',
                    'direction' => 'desc',
                ],
            ],
            'table_columns' => ['date_applied', 'owner_id', 'hours', 'name', 'task_id']
        ],
    ]
];
