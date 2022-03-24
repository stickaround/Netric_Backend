<?php
/**
 * Return browser views for entity of object type 'user_team'
 */
namespace data\browser_views;

use Netric\EntityQuery\Where;

return array(
    'parent_teams'=> array(
        'obj_type' => 'user_team',
        'name' => 'All Teams',
        'description' => 'All Teams',
        'default' => true,
        'conditions' => array(
            'parent_id_zero' => array(
                'blogic' => Where::COMBINED_BY_AND,
                'field_name' => 'parent_id',
                'operator' => Where::OPERATOR_EQUAL_TO,
                'value' => '0'
            )
        ),
        'order_by' => array(
            'name' => array(
                'field_name' => 'name',
                'direction' => 'asc',
            ),
        ),
        'table_columns' => array('name')
    )
);
