<?php
namespace data\entity_definitions;
use Netric\EntityDefinition\Field;

return array(
    'fields' => array(
        'name' => array(
            'title'=>'Name',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'512',
            'readonly'=>false
        ),
        'created_by' => array(
            'title'=>'Created By',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>true
        ),
        'tax_rate' => array(
            'title'=>'Tax %',
            'type'=>Field::TYPE_INTEGER,
            'subtype'=>'',
            'readonly'=>false
        ),
        'amount' => array(
            'title'=>'Amount',
            'type'=>Field::TYPE_NUMBER,
            'subtype'=>'double precision',
            'readonly'=>false
        ),
        'ship_to' => array(
            'title'=>'Ship To',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>false
        ),
        'ship_to_cship' => array(
            'title'=>'Use Shipping Address',
            'type'=>Field::TYPE_BOOL,
            'subtype'=>'',
            'readonly'=>false,
            'default'=>array("value"=>"t", "on"=>"null")
        ),
        'owner_id' => array(
            'title'=>'Owner',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'user'
        ),
        'status_id' => array(
            'title'=>'Status',
            'type'=>Field::TYPE_GROUPING,
            'subtype'=>'customer_order_status',
            'fkey_table'=>array("key"=>"id", "title"=>"name")
        ),
        'customer_id' => array(
            'title'=>'Customer',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'customer'
        ),
        'invoice_id' => array(
            'title'=>'Invoice',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'invoice'
        ),
        'sales_order_id' => array(
            'title'=>'Sales Order Id',
            'type'=>Field::TYPE_INTEGER,
        ),
    ),
);
