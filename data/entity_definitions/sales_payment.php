<?php
namespace data\entity_definitions;
use Netric\EntityDefinition\Field;

return array(
    'fields' => array(
        'amount' => array(
            'title'=>'Amount',
            'type'=>Field::TYPE_NUMBER,
            'subtype'=>'double precision',
            'readonly'=>false
        ),
        'date_paid' => array(
            'title'=>'Date Paid',
            'type'=>Field::TYPE_DATE,
            'subtype'=>'',
            'readonly'=>false
        ),
        'ref' => array(
            'title'=>'Ref / Check Number',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'512',
            'readonly'=>false
        ),
        'payment_method' => array(
            'title'=>'Payment Method',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>false
        ),
        'owner_id' => array(
            'title'=>'Owner',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'user'
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
        'order_id' => array(
            'title'=>'Order',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'order'
        ),
    ),
);
