<?php
/**
 * Return browser views for entity of object type 'workflow_action'
 */
namespace data\browser_views;

use Netric\EntityQuery\Where;
use Netric\EntityDefinition\ObjectTypes;

return [
    'obj_type' => ObjectTypes::WORKFLOW_ACTION,
    'views' => [
        'all_workflow_actions'=> [
            'name' => 'All Actions',
            'description' => 'Display all actions',
            'default' => true,
            'filter_fields' => ['type_name'],
            'order_by' => [
                'order' => [
                    'field_name' => 'entity_id',
                    'direction' => 'asc',
                ],
            ],
            'table_columns' => ['name', 'type_name']
        ],
    ]
];
