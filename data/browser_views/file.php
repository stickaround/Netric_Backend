<?php
/**
 * Return browser views for entity of object type 'note'
 */
namespace data\browser_views;

use Netric\EntityQuery\Where;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\ObjectTypes;

return [
		"obj_type" => ObjectTypes::FILE,
    "filters" => [],
    "views" => [
				'files_and_documents'=> [
					'name' => 'Files & Documents',
					'description' => 'View all files and directories',
					'default' => true,
					'order_by' => [
						'name' => [
								'field_name' => 'name',
								'direction' => 'desc',
							],
					],
					'table_columns' => ['name', 'ts_updated', 'owner_id', 'file_size']
				],
		]
];
