<?php
namespace data\entity_definitions;

return array(
    'revision' => 7,
    'fields' => array(
        'amount' => array(
            'title'=>'Amount',
            'type'=>'number',
            'subtype'=>'double precision',
            'readonly'=>false
        ),
        'date_paid' => array(
            'title'=>'Date Paid',
            'type'=>'date',
            'subtype'=>'',
            'readonly'=>false
        ),
        'ref' => array(
            'title'=>'Ref / Check Number',
            'type'=>'text',
            'subtype'=>'512',
            'readonly'=>false
        ),
        'payment_method' => array(
            'title'=>'Payment Method',
            'type'=>'text',
            'subtype'=>'256',
            'readonly'=>false
        ),
        'owner_id' => array(
            'title'=>'Owner',
            'type'=>'object',
            'subtype'=>'user'
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
        'order_id' => array(
            'title'=>'Order',
            'type'=>'object',
            'subtype'=>'order'
        ),
    ),
);
