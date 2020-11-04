<?php
/**
 * Return browser views for entity of object type 'folder'
 */
namespace data\browser_views;

use Netric\EntityQuery\Where;

return array(
    'default'=> array(
		'obj_type' => 'folder',
		'name' => 'Default View',
		'description' => '',
		'default' => true,
		'order_by' => array(
			'sort_order' => array(
    			'field_name' => 'sort_order',
    			'direction' => 'desc',
    		),
		),
		'table_columns' => array('name')
    ),
);
