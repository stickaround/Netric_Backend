<?php
/**
 * Return browser views for entity of object type 'cms_page'
 */
namespace data\browser_views;

use Netric\EntityQuery\Where;
use Netric\EntityDefinition\ObjectTypes;

return [
		"obj_type" => ObjectTypes::PAGE,
    "filters" => [],
    "views" => [
			'all_pages'=> [
				'name' => 'All Pages',
				'description' => 'Display all pages',
				'default' => true,        
				'order_by' => [
					'name' => [
							'field_name' => 'name',
							'direction' => 'asc',
						],
				],
				'table_columns' => ['name', 'uname', 'title', 'parent_id']
			],
		]
];
