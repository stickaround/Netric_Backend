<?php
namespace data\entity_definitions;

return array(
    'list_title' => 'first_name',
    'fields' => array(
        'name' => array(
            'title'=>'Name',
            'type'=>'auto',
            'subtype'=>'',
            'readonly'=>true,
            'default'=>array(
                "value"=>"Untitled",
                "on"=>"null",
                "coalesce"=>array(
                    array(
                        "first_name",
                        "last_name"
                    ),
                    "company"
                ),
            ),
        ),
        'first_name' => array(
            'title'=>'First Name',
            'type'=>'text',
            'subtype'=>'256',
            'readonly'=>false
        ),
        'last_name' => array(
            'title'=>'Last Name',
            'type'=>'text',
            'subtype'=>'256',
            'readonly'=>false
        ),
        'email' => array(
            'title'=>'Email',
            'type'=>'text',
            'subtype'=>'email',
            'readonly'=>false
        ),
        'phone' => array(
            'title'=>'Phone',
            'type'=>'text',
            'subtype'=>'64',
            'readonly'=>false,
            'mask'=>'phone_dash'
        ),
        'phone2' => array(
            'title'=>'Phone 2',
            'type'=>'text',
            'subtype'=>'64',
            'readonly'=>false,
            'mask'=>'phone_dash'
        ),
        'phone3' => array(
            'title'=>'Phone 3',
            'type'=>'text',
            'subtype'=>'64',
            'readonly'=>false,
            'mask'=>'phone_dash'
        ),
        'fax' => array(
            'title'=>'Fax',
            'type'=>'text',
            'subtype'=>'64',
            'readonly'=>false
        ),
        'street' => array(
            'title'=>'Street',
            'type'=>'text',
            'subtype'=>'128',
            'readonly'=>false
        ),
        'street2' => array(
            'title'=>'Street 2',
            'type'=>'text',
            'subtype'=>'128',
            'readonly'=>false
        ),
        'city' => array(
            'title'=>'City',
            'type'=>'text',
            'subtype'=>'128',
            'readonly'=>false
        ),
        'state' => array(
            'title'=>'State',
            'type'=>'text',
            'subtype'=>'64',
            'readonly'=>false
        ),
        'zip' => array(
            'title'=>'Zip',
            'type'=>'text',
            'subtype'=>'zipcode',
            'readonly'=>false
        ),
        'notes' => array(
            'title'=>'Notes',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>false
        ),
        'company' => array(
            'title'=>'Company',
            'type'=>'text',
            'subtype'=>'256',
            'readonly'=>false
        ),
        'title' => array(
            'title'=>'Job Title',
            'type'=>'text',
            'subtype'=>'64',
            'readonly'=>false
        ),
        'website' => array(
            'title'=>'Website',
            'type'=>'text',
            'subtype'=>'128',
            'readonly'=>false
        ),
        'country' => array(
            'title'=>'Country',
            'type'=>'text',
            'subtype'=>'512',
            'readonly'=>false
        ),
        'f_converted' => array(
            'title'=>'Converted',
            'type'=>'bool',
            'subtype'=>'',
            'readonly'=>true
        ),
        'f_seen' => array(
            'title'=>'Seen',
            'type'=>'bool',
            'subtype'=>'',
            'readonly'=>true,
            "default"=>array(
                "value"=>'f',
                "on"=>"null"
            )
        ),
        'campaign_id' => array(
            'title'=>'Campaign',
            'type'=>'object',
            'subtype'=>'marketing_campaign'
        ),
        'queue_id' => array(
            'title'=>'Queue',
            'type'=>'fkey',
            'subtype'=>'object_groupings',
            'fkey_table'=>array("key"=>"id", "title"=>"name")
        ),
        'owner_id' => array(
            'title'=>'Owner',
            'type'=>'object',
            'subtype'=>'user',
            'default'=>array("value"=>"-3", "on"=>"null")
        ),
        'source_id' => array(
            'title'=>'Source',
            'type'=>'fkey',
            'subtype'=>'object_groupings',
            'fkey_table'=>array("key"=>"id", "title"=>"name")
        ),
        'rating_id' => array(
            'title'=>'Rating',
            'type'=>'fkey',
            'subtype'=>'object_groupings',
            'fkey_table'=>array("key"=>"id", "title"=>"name")
        ),
        'status_id' => array(
            'title'=>'Status',
            'type'=>'fkey',
            'subtype'=>'object_groupings',
            'fkey_table'=>array("key"=>"id", "title"=>"name")
        ),
        'class_id' => array(
            'title'=>'Class',
            'type'=>'fkey',
            'subtype'=>'object_groupings',
            'fkey_table'=>array("key"=>"id", "title"=>"name")
        ),
        'converted_opportunity_id' => array(
            'title'=>'Opportunity',
            'type'=>'object',
            'subtype'=>'opportunity'
        ),
        'converted_customer_id' => array(
            'title'=>'Customer',
            'type'=>'object',
            'subtype'=>'customer'
        ),
        'ts_converted' => array(
            'title'=>'Time Converted',
            'type'=>'timestamp',
            'subtype'=>'',
            'readonly'=>true
        ),
    ),
);
