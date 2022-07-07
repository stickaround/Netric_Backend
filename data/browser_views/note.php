<?php

/**
 * Return browser views for entity of object type 'note'
 */
namespace data\browser_views;

use Netric\Entity\ObjType\UserEntity;
use Netric\EntityQuery\Where;
use Netric\EntityDefinition\ObjectTypes;

return [
    "obj_type" => ObjectTypes::NOTE,
    "filters" => [],
    "views" => [
        'default' => [        
            'name' => 'My Notes',
            'description' => 'My Notes',
            'default' => true,
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
                    'field_name' => 'ts_updated',
                    'direction' => 'desc',
                ],
            ],
            'table_columns' => ['name', 'ts_updated', 'body']
        ],
    ]
];
