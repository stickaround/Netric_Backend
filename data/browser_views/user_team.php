<?php
/**
 * Return browser views for entity of object type 'user'
 */
namespace data\browser_views;

use Netric\Entity\ObjType\UserEntity;
use Netric\EntityQuery\Where;

return array(
    'active' => [
        'obj_type' => 'user',
        'name' => 'Active',
        'description' => 'Active Users',
        'default' => true,
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
        'obj_type' => 'user',
        'name' => 'Inactive Users',
        'description' => 'Inactive Users',
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
        'obj_type' => 'user',
        'name' => 'All Users',
        'description' => 'All Users',
        'default' => false,
        'order_by' => [
            'name' => [
                'field_name' => 'full_name',
                'direction' => 'asc',
            ],
        ],
        'table_columns' => ['full_name', 'name', 'last_login', 'team_id', 'manager_id']
    ],
    // 'parent_teams'=> array(
    //     'obj_type' => 'user_team',
    //     'name' => 'All Teams',
    //     'description' => 'All Teams',
    //     'default' => true,
    //     'conditions' => array(
    //         'parent_id_empty' => array(
    //             'blogic' => Where::COMBINED_BY_AND,
    //             'field_name' => 'parent_id',
    //             'operator' => Where::OPERATOR_EQUAL_TO,
    //             'value' => ''
    //         ),
    //         'parent_id_zero' => array(
    //             'blogic' => Where::COMBINED_BY_OR,
    //             'field_name' => 'parent_id',
    //             'operator' => Where::OPERATOR_EQUAL_TO,
    //             'value' => ''
    //         )
    //     ),
    //     'order_by' => array(
    //         'name' => array(
    //             'field_name' => 'name',
    //             'direction' => 'asc',
    //         ),
    //     ),
    //     'table_columns' => array('name')
    // ),
    // 'parent_teams' => [
    //     'obj_type' => 'user_team',
    //     'name' => 'All Teams',
    //     'description' => 'All Teams',
    //     'default' => true,
    //     'order_by' => [
    //         'name' => [
    //             'field_name' => 'full_name',
    //             'direction' => 'asc',
    //         ],
    //     ],
    //     'table_columns' => ['full_name', 'name', 'last_login', 'team_id', 'manager_id']
    // ],
);
