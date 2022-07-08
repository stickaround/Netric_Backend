<?php
/**
 * Return browser views for entity of object type 'email_message_attachment'
 */
namespace data\browser_views;

use Netric\EntityQuery\Where;
use Netric\EntityDefinition\ObjectTypes;

return [
		"obj_type" => ObjectTypes::EMAIL_MESSAGE_ATTACHMENT,
		"filters" => [],
    "views" => [
			'all_email_attachments'=> [		
				'name' => 'All Attachments',
				'description' => 'Message Attachments',
				'default' => true,
				'order_by' => [
					'sort_order' => [
							'field_name' => 'sort_order',
							'direction' => 'desc',
						],
				],
				'table_columns' => ['name', 'filename', 'phone_home', 'phone_work', 'email_default', 'image_id']
    ],
		]
];
