<?php
namespace data\entity_definitions;

return array(
    'revision' => 10,
    'fields' => array(
        'name' => array(
            'title'=>'Name',
            'type'=>'text',
            'subtype'=>'512',
            'readonly'=>false
        ),
        'created_by' => array(
            'title'=>'Created By',
            'type'=>'text',
            'subtype'=>'256',
            'readonly'=>true
        ),
        'tax_rate' => array(
            'title'=>'Tax %',
            'type'=>'integer',
            'subtype'=>'',
            'readonly'=>false
        ),
        'amount' => array(
            'title'=>'Amount',
            'type'=>'number',
            'subtype'=>'double precision',
            'readonly'=>false
        ),
        'ship_to' => array(
            'title'=>'Ship To',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>false
        ),
        'ship_to_cship' => array(
            'title'=>'Use Shipping Address',
            'type'=>'bool',
            'subtype'=>'',
            'readonly'=>false,
            'default'=>array("value"=>"t", "on"=>"null")
        ),
        'owner_id' => array(
            'title'=>'Owner',
            'type'=>'object',
            'subtype'=>'user'
        ),
        'status_id' => array(
            'title'=>'Status',
            'type'=>'fkey',
            'subtype'=>'customer_order_status',
            'fkey_table'=>array("key"=>"id", "title"=>"name")
        ),
        'customer_id' => array(
            'title'=>'Customer',
            'type'=>'object',
            'subtype'=>'customer'
        ),
        'invoice_id' => array(
            'title'=>'Invoice',
            'type'=>'object',
            'subtype'=>'invoice'
        ),
        'sales_order_id' => array(
            'title'=>'Sales Order Id',
            'type'=>'integer'
        ),
    ),
);
