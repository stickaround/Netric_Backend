<?php

/**
 * Return browser views for entity of object type 'wall_post'
 */

namespace data\browser_views;

use Netric\Entity\ObjType\UserEntity;
use Netric\EntityQuery\Where;
use Netric\EntityDefinition\ObjectTypes;

return [
    "obj_type" => ObjectTypes::WALL_POST,
    "filters" => [],
    "views" => [
        'news_feed' => [
            'obj_type' => 'wall_post',
            'name' => 'News Feed',
            'description' => '',
            'default' => true,
            'order_by' => [
                'sort_order' => [
                    'field_name' => 'ts_entered',
                    'direction' => 'desc',
                ],
            ],
            'table_columns' => ['owner_id', 'ts_entered', 'obj_reference', 'comment', 'notified', 'sent_by']
        ],
    
        'my_wall_posts' => [
            'obj_type' => 'wall_post',
            'name' => 'My Status Updates',
            'description' => '',
            'default' => false,
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
                    'field_name' => 'ts_entered',
                    'direction' => 'desc',
                ],
            ],
            'table_columns' => ['owner_id', 'ts_entered', 'obj_reference', 'comment', 'notified', 'sent_by']
        ],
    ]
];
