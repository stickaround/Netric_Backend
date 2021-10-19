<?php

namespace data\entity_definitions;

use Netric\EntityDefinition\Field;
use Netric\EntityDefinition\ObjectTypes;

return array(
    'fields' => array(
        'name' => array(
            'title' => 'Name',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false,
            "required" => true,
        ),
        'number' => array(
            'title' => 'Number',
            'type' => Field::TYPE_TEXT,
            'subtype' => '512',
            'readonly' => true
        ),
        'created_by' => array(
            'title' => 'Created By',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => true
        ),
        'updated_by' => array(
            'title' => 'Updated By',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => true
        ),
        'notes_line1' => array(
            'title' => 'Notes Line 1',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => true
        ),
        'notes_line2' => array(
            'title' => 'Notes Line 2',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => true
        ),
        'footer_line1' => array(
            'title' => 'Footer',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => true
        ),
        'payment_terms' => array(
            'title' => 'Payment Terms',
            'type' => Field::TYPE_TEXT,
            'subtype' => '128',
            'readonly' => false
        ),
        'tax_rate' => array(
            'title' => 'Tax %',
            'type' => Field::TYPE_INTEGER,
            'subtype' => '',
            'readonly' => false
        ),
        'amount' => array(
            'title' => 'Amount',
            'type' => Field::TYPE_NUMBER,
            'subtype' => 'double precision',
            'readonly' => false
        ),
        'send_to' => array(
            'title' => 'Send To',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => false
        ),
        'reference' => array(
            'title' => 'Reference',
            'type' => Field::TYPE_TEXT,
            'subtype' => '128',
            'readonly' => false
        ),
        'send_to_cbill' => array(
            'title' => 'Use Billing Address',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => false,
            'default' => array("value" => "t", "on" => "null")
        ),
        'type' => array(
            'title' => 'Type',
            'type' => Field::TYPE_TEXT,
            'subtype' => '32',
            'readonly' => false,
            'optional_values' => array("r" => "Receivable", "p" => "Payable"),
            'default' => array("value" => "r", "on" => "null")
        ),
        'date_due' => array(
            'title' => 'Due Date',
            'type' => Field::TYPE_DATE,
            'subtype' => '',
            'readonly' => false
        ),
        'status_id' => array(
            'title' => 'Status',
            'type' => Field::TYPE_GROUPING,
            'subtype' => 'object_groupings',
        ),
        'template_id' => array(
            'title' => 'Template',
            'type' => Field::TYPE_OBJECT,
            'subtype' => 'invoice_template',
            'fkey_table' => array("key" => "id", "title" => "name")
        ),
        'customer_id' => array(
            'title' => 'Customer',
            'type' => Field::TYPE_OBJECT,
            'subtype' => ObjectTypes::CONTACT
        ),
        'sales_order_id' => array(
            'title' => 'Order',
            'type' => Field::TYPE_OBJECT,
            'subtype' => 'sales_order'
        ),
    ),
);
