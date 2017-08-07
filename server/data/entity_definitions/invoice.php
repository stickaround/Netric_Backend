<?php
namespace data\entity_definitions;

return array(
    'revision' => 28,
    'fields' => array(
        'name' => array(
            'title'=>'Name',
            'type'=>'text',
            'subtype'=>'256',
            'readonly'=>false,
            "required"=>true,
        ),
        'number' => array(
            'title'=>'Number',
            'type'=>'text',
            'subtype'=>'512',
            'readonly'=>true
        ),
        'created_by' => array(
            'title'=>'Created By',
            'type'=>'text',
            'subtype'=>'256',
            'readonly'=>true
        ),
        'updated_by' => array(
            'title'=>'Updated By',
            'type'=>'text',
            'subtype'=>'256',
            'readonly'=>true
        ),
        'notes_line1' => array(
            'title'=>'Notes Line 1',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>true
        ),
        'notes_line2' => array(
            'title'=>'Notes Line 2',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>true
        ),
        'footer_line1' => array(
            'title'=>'Footer Line 1',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>true
        ),
        'payment_terms' => array(
            'title'=>'Payment Terms',
            'type'=>'text',
            'subtype'=>'128',
            'readonly'=>false
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
        'send_to' => array(
            'title'=>'Send To',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>false
        ),
        'reference' => array(
            'title'=>'Reference',
            'type'=>'text',
            'subtype'=>'128',
            'readonly'=>false
        ),
        'send_to_cbill' => array(
            'title'=>'Use Billing Address',
            'type'=>'bool',
            'subtype'=>'',
            'readonly'=>false,
            'default'=>array("value"=>"t", "on"=>"null")
        ),
        'type' => array(
            'title'=>'Type',
            'type'=>'text',
            'subtype'=>'32',
            'readonly'=>false,
            'optional_values'=>array("r"=>"Receivable", "p"=>"Payable"),
            'default'=>array("value"=>"r", "on"=>"null")
        ),
        'date_due' => array(
            'title'=>'Due Date',
            'type'=>'date',
            'subtype'=>'',
            'readonly'=>false
        ),
        'owner_id' => array(
            'title'=>'Owner',
            'type'=>'object',
            'subtype'=>'user'
        ),
        'status_id' => array(
            'title'=>'Status',
            'type'=>'fkey',
            'subtype'=>'customer_invoice_status',
            'fkey_table'=>array("key"=>"id", "title"=>"name")
        ),
        'template_id' => array(
            'title'=>'Template',
            'type'=>'fkey',
            'subtype'=>'customer_invoice_templates',
            'fkey_table'=>array("key"=>"id", "title"=>"name")
        ),
        'customer_id' => array(
            'title'=>'Customer',
            'type'=>'object',
            'subtype'=>'customer'
        ),
        'sales_order_id' => array(
            'title'=>'Order',
            'type'=>'object',
            'subtype'=>'sales_order'
        ),
    ),
);
