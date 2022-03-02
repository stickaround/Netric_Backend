<?php

/**
 * Return browser views for entity of object type 'note'
 */

namespace data\browser_views;

use Netric\Entity\ObjType\UserEntity;
use Netric\Entity\ObjType\TaskEntity;
use Netric\EntityDefinition\ObjectTypes;
use Netric\EntityQuery\Where;

return [
    'default' => [
        'obj_type' => ObjectTypes::TASK,
        'name' => 'All Tasks',
        'description' => 'All Tasks',
        'default' => false,
        'order_by' => [
            'ts_entered' => [
                'field_name' => 'ts_entered',
                'direction' => 'desc',
            ],
            'deadline' => [
                'field_name' => 'deadline',
                'direction' => 'asc'
            ],
        ],
        'table_columns' => [
            'name', 'project', 'status_id', 'deadline', 'owner_id'
        ]
    ],
    'my_tasks' => [
        'obj_type' => ObjectTypes::TASK,
        'name' => 'My Incomplete Tasks',
        'description' => 'Incomplete tasks assigned to me',
        'default' => true,
        'conditions' => [
            'user' => [
                'blogic' => Where::COMBINED_BY_AND,
                'field_name' => 'owner_id',
                'operator' => Where::OPERATOR_EQUAL_TO,
                'value' => UserEntity::USER_CURRENT,
            ],
            'status_id_com' => [
                'blogic' => Where::COMBINED_BY_AND,
                'field_name' => 'status_id',
                'operator' => Where::OPERATOR_NOT_EQUAL_TO,
                'value' => TaskEntity::STATUS_COMPLETED
            ],
            'status_id_def' => [
                'blogic' => Where::COMBINED_BY_AND,
                'field_name' => 'status_id',
                'operator' => Where::OPERATOR_NOT_EQUAL_TO,
                'value' => TaskEntity::STATUS_DEFERRED
            ],
        ],
        'filter_key' => 'project',
        'group_first_order_by' => true,
        'order_by' => [
            'status_id' => [
                'field_name' => 'status_id',
                'direction' => 'desc',
            ],
            'date' => [
                'field_name' => 'ts_entered',
                'direction' => 'desc',
            ],
            'deadline' => [
                'field_name' => 'deadline',
                'direction' => 'asc'
            ],
        ],
        'table_columns' => ['name', 'project', 'status_id', 'deadline']
    ],

    'my_tasks_due_today' => [
        'obj_type' => ObjectTypes::TASK,
        'name' => 'My Incomplete Tasks (due today)',
        'description' => 'Incomplete tasks assigned to me that are due today',
        'default' => false,
        'conditions' => [
            'user' => [
                'blogic' => Where::COMBINED_BY_AND,
                'field_name' => 'owner_id',
                'operator' => Where::OPERATOR_EQUAL_TO,
                'value' => UserEntity::USER_CURRENT,
            ],
            'is_closed' => [
                'blogic' => Where::COMBINED_BY_AND,
                'field_name' => 'is_closed',
                'operator' => Where::OPERATOR_NOT_EQUAL_TO,
                'value' => true
            ],
            'deadline' => [
                'blogic' => Where::COMBINED_BY_AND,
                'field_name' => 'deadline',
                'operator' => Where::OPERATOR_LESS_THAN_OR_EQUAL_TO,
                'value' => 'now'
            ],
        ],
        'order_by' => [
            'date' => [
                'field_name' => 'ts_entered',
                'direction' => 'desc',
            ],
            'deadline' => [
                'field_name' => 'deadline',
                'direction' => 'asc'
            ],
        ],
        'table_columns' => ['name', 'project', 'status_id', 'deadline']
    ],

    'all_my_tasks' => [
        'obj_type' => ObjectTypes::TASK,
        'name' => 'All My Tasks',
        'description' => 'All tasks assigned to me',
        'default' => false,
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
                'field_name' => 'ts_entered',
                'direction' => 'desc',
            ],
            'deadline' => [
                'field_name' => 'deadline',
                'direction' => 'asc'
            ],
        ],
        'table_columns' => ['name', 'project', 'status_id', 'deadline']
    ],

    'tasks_i_have_assigned' => [
        'obj_type' => ObjectTypes::TASK,
        'name' => 'Tasks I Have Assigned',
        'description' => 'Tasks that were created by me but assigned to someone else',
        'default' => false,
        'group_first_order_by' => true,
        'conditions' => [
            'creator' => [
                'blogic' => Where::COMBINED_BY_AND,
                'field_name' => 'creator_id',
                'operator' => Where::OPERATOR_EQUAL_TO,
                'value' => UserEntity::USER_CURRENT,
            ],
            'user' => [
                'blogic' => Where::COMBINED_BY_AND,
                'field_name' => 'owner_id',
                'operator' => Where::OPERATOR_NOT_EQUAL_TO,
                'value' => UserEntity::USER_CURRENT,
            ],
            'is_closed' => [
                'blogic' => Where::COMBINED_BY_AND,
                'field_name' => 'is_closed',
                'operator' => Where::OPERATOR_NOT_EQUAL_TO,
                'value' => true
            ],
        ],
        'order_by' => [
            'date' => [
                'field_name' => 'ts_entered',
                'direction' => 'desc',
            ],
            'deadline' => [
                'field_name' => 'deadline',
                'direction' => 'asc'
            ],
        ],
        'table_columns' => ['name', 'project', 'status_id', 'deadline', 'owner_id']
    ],

    'unassigned_incomplete' => [
        'obj_type' => ObjectTypes::TASK,
        'name' => 'Incomplete and Unassigned',
        'description' => 'Tasks that have not been completed yet and do not have an owner',
        'default' => false,
        'group_first_order_by' => true,
        'conditions' => [
            'user' => [
                'blogic' => Where::COMBINED_BY_AND,
                'field_name' => 'owner_id',
                'operator' => Where::OPERATOR_NOT_EQUAL_TO,
                'value' => '',
            ],
            'is_closed' => [
                'blogic' => Where::COMBINED_BY_AND,
                'field_name' => 'is_closed',
                'operator' => Where::OPERATOR_NOT_EQUAL_TO,
                'value' => true,
            ],
        ],
        'order_by' => [
            'date' => [
                'field_name' => 'ts_entered',
                'direction' => 'desc',
            ],
            'deadline' => [
                'field_name' => 'deadline',
                'direction' => 'asc'
            ],
        ],
        'table_columns' => ['name', 'project', 'status_id', 'deadline', 'owner_id']
    ],

    'all_incomplete_tasks' => [
        'obj_type' => ObjectTypes::TASK,
        'name' => 'All Incomplete Tasks',
        'description' => 'All Tasks that have not yet been completed',
        'default' => false,
        'conditions' => [
            'is_closed' => [
                'blogic' => Where::COMBINED_BY_AND,
                'field_name' => 'is_closed',
                'operator' => Where::OPERATOR_NOT_EQUAL_TO,
                'value' => true
            ],
        ],
        'group_first_order_by' => true,
        'order_by' => [
            'status_id' => [
                'field_name' => 'status_id',
                'direction' => 'asc',
            ],
            'date' => [
                'field_name' => 'ts_entered',
                'direction' => 'desc',
            ],
            'deadline' => [
                'field_name' => 'deadline',
                'direction' => 'asc'
            ],
        ],
        'table_columns' => ['name', 'project', 'status_id', 'deadline', 'owner_id']
    ],

    'all_tasks' => [
        'obj_type' => ObjectTypes::TASK,
        'name' => 'All Tasks',
        'description' => 'All Tasks',
        'default' => false,
        'order_by' => [
            'date' => [
                'field_name' => 'ts_entered',
                'direction' => 'desc',
            ],
            'deadline' => [
                'field_name' => 'deadline',
                'direction' => 'asc'
            ],
        ],
        'table_columns' => ['name', 'project', 'status_id', 'deadline', 'owner_id']
    ],
];
