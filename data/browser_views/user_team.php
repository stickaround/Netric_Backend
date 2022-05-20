<?php

/**
 * Return browser views for entity of object type 'user_team'
 */

namespace data\browser_views;

return [
    'all-teams' => [
        'obj_type' => 'user_team',
        'name' => 'All Teams',
        'description' => 'All Teams',
        'default' => true,
        'order_by' => [
            'name' => [
                'field_name' => 'name',
                'direction' => 'asc',
            ],
        ],
        'table_columns' => ['name']
    ]
];
