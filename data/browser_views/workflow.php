<?php
/**
 * Return browser views for entity of object type 'workflow'
 */
namespace data\browser_views;

use Netric\EntityQuery\Where;
use Netric\EntityDefinition\ObjectTypes;

return [
    'obj_type' => ObjectTypes::WORKFLOW,
    'views' => [
        'all_workflows'=> [
            'name' => 'All Workflows',
            'description' => 'Display all workflows',
            'default' => true,
            'filter_fields' => ['f_active', 'object_type'],
            'order_by' => [
                'date' => [
                    'field_name' => 'name',
                    'direction' => 'asc',
                ],
            ],
            'table_columns' => ['name', 'object_type', 'f_active']
        ],
    ]
];
