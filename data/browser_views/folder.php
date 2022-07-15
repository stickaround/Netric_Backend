<?php
/**
 * Return browser views for entity of object type 'folder'
 */
namespace data\browser_views;

use Netric\EntityQuery\Where;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\ObjectTypes;

return [
		'obj_type' => ObjectTypes::FOLDER,
    'views' => [
			'default'=> [
				'name' => 'Default View',
				'description' => '',
				'default' => true,
				'filter_fields' => ['owner_id'],
				'order_by' => [
					'sort_order' => [
							'field_name' => 'sort_order',
							'direction' => 'desc',
						],
				],
				'table_columns' => ['name']
    ],
		]
];
