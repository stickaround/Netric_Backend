<?php

/**
 * Return browser views for entity of object type 'cms_site'
 */

namespace data\browser_views;

use Netric\EntityQuery\Where;
use Netric\EntityDefinition\ObjectTypes;

return [
	"obj_type" => ObjectTypes::SITE,
	"filters" => [],
	"views" => [
		'all_sites' => [
			'name' => 'All Sites',
			'description' => 'Display all available sites',
			'default' => true,
			'order_by' => [
				'name' => [
					'field_name' => 'name',
					'direction' => 'asc',
				],
			],
			'table_columns' => ['name', 'url']
		],
	]
];
