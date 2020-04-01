<?php

/**
 * Return browser views for entity of object type 'note'
 */
namespace data\browser_views;

use Netric\Entity\ObjType\UserEntity;
use Netric\EntityQuery\Where;

return array(
    'default' => array(
        'obj_type' => 'note',
        'name' => 'My Notes',
        'description' => 'My Notes',
        'default' => true,
        'conditions' => array(
            'user' => array(
                'blogic' => Where::COMBINED_BY_AND,
                'field_name' => 'owner_id',
                'operator' => Where::OPERATOR_EQUAL_TO,
                'value' => UserEntity::USER_CURRENT,
            ),
        ),
        'order_by' => array(
            'date' => array(
                'field_name' => 'ts_updated',
                'direction' => 'desc',
            ),
        ),
        'table_columns' => array('name', 'ts_updated', 'body')
    ),
);
