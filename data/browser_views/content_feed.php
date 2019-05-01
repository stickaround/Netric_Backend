<?php
/**
 * Return browser views for entity of object type 'content_feed'
 */
namespace data\browser_views;

use Netric\EntityQuery\Where;

return array(
    'all_feeds'=> array(
		'obj_type' => 'content_feed',
		'name' => 'All Content Feeds',
		'description' => 'All Content Feeds',
		'default' => true,
		'order_by' => array(
			'date' => array(
    			'field_name' => 'ts_updated',
    			'direction' => 'desc',
    		),
		),
		'table_columns' => array('title', 'ts_updated')
    ),
);
