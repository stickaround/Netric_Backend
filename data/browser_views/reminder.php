<?php
/**
 * Return browser views for entity of object type 'reminder'
 */

namespace data\browser_views;

use Netric\Entity\ObjType\UserEntity;
use Netric\EntityQuery\Where;
use Netric\EntityDefinition\ObjectTypes;

return [
    'obj_type' => ObjectTypes::REMINDER,
    'views' => [
        'my_reminders' => [
            'name' => 'My Reminders',
            'description' => 'Display all my reminders',
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
                    'direction' => 'asc',
                ],
            ],
            'table_columns' => ['name', 'ts_execute']
        ],
    
        'all_reminders' => [
            'name' => 'All Reminders',
            'description' => 'Display all reminders',
            'filter_fields' => ['owner_id'],
            'default' => false,
            'order_by' => [
                'sort_order' => [
                    'field_name' => 'sort_order',
                    'direction' => 'asc',
                ],
            ],
            'table_columns' => ['name', 'ts_execute']
        ],
    ]
];
