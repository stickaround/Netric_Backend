<?php
/**
 * Return browser views for entity of object type 'cms_page_template'
 */
namespace data\browser_views;

use Netric\EntityQuery\Where;
use Netric\EntityDefinition\ObjectTypes;

return [
		'obj_type' => ObjectTypes::PAGE_TEMPLATE,    
    'views' => [
			'all_templates'=> [		
				'name' => 'All Templates',
				'description' => 'Display all templates',
				'default' => true,        
				'filter_fields' => [],
				'order_by' => [
					'name' => [
							'field_name' => 'name',
							'direction' => 'desc',
						],
				],
				'table_columns' => ['name', 'type']
				],
		]
];
