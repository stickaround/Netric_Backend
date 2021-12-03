<?php

/**
 * Return browser views for entity of object type 'project'
 */

namespace data\browser_views;

use Netric\Entity\ObjType\UserEntity;
use Netric\EntityQuery\Where;

return [
    'my_open_projects' => [
        'obj_type' => 'project',
        'name' => 'My Open Projects',
        'description' => '',
        'default' => true,
        'conditions' => [
            'members' => [
                'blogic' => Where::COMBINED_BY_AND,
                'field_name' => 'members',
                'operator' => Where::OPERATOR_EQUAL_TO,
                'value' => UserEntity::USER_CURRENT,
            ],
            'completed' => [
                'blogic' => Where::COMBINED_BY_AND,
                'field_name' => 'date_completed',
                'operator' => Where::OPERATOR_EQUAL_TO,
                'value' => ''
            ],
        ],
        'order_by' => [
            'name' => [
                'field_name' => 'name',
                'direction' => 'asc',
            ],
        ],
        'table_columns' => [
            'name', 'priority', 'date_started', 'date_deadline', 'date_completed'
        ],
    ],

    'all_projects' => [
        'obj_type' => 'project',
        'name' => 'All Projects',
        'description' => '',
        'default' => false,
        'order_by' => [
            'name' => [
                'field_name' => 'name',
                'direction' => 'asc',
            ]
        ],
        'table_columns' => [
            'name', 'priority', 'date_started', 'date_deadline', 'date_completed'
        ]
    ],

    'my_closed_projects' => [
        'obj_type' => 'project',
        'name' => 'My Closed Projects',
        'description' => '',
        'default' => false,
        'conditions' => [
            'members' => [
                'blogic' => Where::COMBINED_BY_AND,
                'field_name' => 'members',
                'operator' => Where::OPERATOR_EQUAL_TO,
                'value' => UserEntity::USER_CURRENT,
            ],
            'completed' => [
                'blogic' => Where::COMBINED_BY_AND,
                'field_name' => 'date_completed',
                'operator' => Where::OPERATOR_NOT_EQUAL_TO,
                'value' => ''
            ],
        ],
        'order_by' => [
            'name' => [
                'field_name' => 'name',
                'direction' => 'asc',
            ],
        ],
        'table_columns' => [
            'name', 'priority', 'date_started', 'date_deadline',
            'date_completed'
        ]
    ],

    'all_open_projects' => [
        'obj_type' => 'project',
        'name' => 'All Open Projects',
        'description' => '',
        'default' => false,
        'conditions' => [
            'completed' => [
                'blogic' => Where::COMBINED_BY_AND,
                'field_name' => 'date_completed',
                'operator' => Where::OPERATOR_EQUAL_TO,
                'value' => ''
            ],
        ],
        'order_by' => [
            'name' => [
                'field_name' => 'name',
                'direction' => 'asc',
            ],
        ],
        'table_columns' => [
            'name', 'priority', 'date_started', 'date_deadline', 'date_completed'
        ]
    ],

    'ongoing_projects' => [
        'obj_type' => 'project',
        'name' => 'Ongoing Projects (no deadline)',
        'description' => '',
        'default' => false,
        'conditions' => [
            'deadline' => [
                'blogic' => Where::COMBINED_BY_AND,
                'field_name' => 'date_deadline',
                'operator' => Where::OPERATOR_EQUAL_TO,
                'value' => ''
            ],
        ],
        'order_by' => [
            'name' => [
                'field_name' => 'name',
                'direction' => 'asc',
            ],
        ],
        'table_columns' => ['name', 'priority', 'date_started', 'date_deadline', 'date_completed']
    ],

    'late_projects' => [
        'obj_type' => 'project',
        'name' => 'Late Projects',
        'description' => '',
        'default' => false,
        'conditions' => [
            'deadline' => [
                'blogic' => Where::COMBINED_BY_AND,
                'field_name' => 'date_deadline',
                'operator' => Where::OPERATOR_LESS_THAN,
                'value' => 'now'
            ],
        ],
        'order_by' => [
            'name' => [
                'field_name' => 'name',
                'direction' => 'asc',
            ],
        ],
        'table_columns' => ['name', 'priority', 'date_started', 'date_deadline', 'date_completed']
    ],
];
