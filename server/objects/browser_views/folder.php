<?php
/**
 * Return browser views for entity of object type 'folder'
 */
namespace objects\browser_views;

use Netric\EntityQuery\Where;

return array(
    'default'=> array(
        'obj_type' => 'folder',
    	'name' => 'Default View',
		'description' => '',
    	'default' => true,
		'order_by' => array(
			'date' => array(
    			'field_name' => 'ts_entered',
    			'direction' => 'desc',
    		),
		),
		'table_columns' => array('id')
    ),
);
