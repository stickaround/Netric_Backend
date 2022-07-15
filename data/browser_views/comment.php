<?php

/**
 * Return browser views for entity of object type 'comment'
 */

namespace data\browser_views;

use Netric\EntityQuery\Where;
use Netric\EntityDefinition\ObjectTypes;

return [
	"obj_type" => ObjectTypes::COMMENT,
	"filters" => [],
	"views" => [
		'default' => [
			'name' => 'Comments',
			'description' => '',
			'default' => true,
			'order_by' => [
				'sort_order' => [
					'field_name' => 'sort_order',
					'direction' => 'asc',
				],
			],
			'table_columns' => ['owner_id', 'ts_entered', 'obj_reference', 'comment', 'notified', 'sent_by']
		],
	]
];
