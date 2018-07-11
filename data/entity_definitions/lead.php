<?php
namespace data\entity_definitions;

use Netric\EntityDefinition\Field;

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
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>false
        ),
        'last_name' => array(
            'title'=>'Last Name',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>false
        ),
        'email' => array(
            'title'=>'Email',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'email',
            'readonly'=>false
        ),
        'phone' => array(
            'title'=>'Phone',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'64',
            'readonly'=>false,
            'mask'=>'phone_dash'
        ),
        'phone2' => array(
            'title'=>'Phone 2',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'64',
            'readonly'=>false,
            'mask'=>'phone_dash'
        ),
        'phone3' => array(
            'title'=>'Phone 3',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'64',
            'readonly'=>false,
            'mask'=>'phone_dash'
        ),
        'fax' => array(
            'title'=>'Fax',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'64',
            'readonly'=>false
        ),
        'street' => array(
            'title'=>'Street',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'128',
            'readonly'=>false
        ),
        'street2' => array(
            'title'=>'Street 2',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'128',
            'readonly'=>false
        ),
        'city' => array(
            'title'=>'City',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'128',
            'readonly'=>false
        ),
        'state' => array(
            'title'=>'State',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'64',
            'readonly'=>false
        ),
        'zip' => array(
            'title'=>'Zip',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'zipcode',
            'readonly'=>false
        ),
        'notes' => array(
            'title'=>'Notes',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>false
        ),
        'company' => array(
            'title'=>'Company',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>false
        ),
        'title' => array(
            'title'=>'Job Title',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'64',
            'readonly'=>false
        ),
        'website' => array(
            'title'=>'Website',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'128',
            'readonly'=>false
        ),
        'country' => array(
            'title'=>'Country',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'512',
            'readonly'=>false
        ),
        'f_converted' => array(
            'title'=>'Converted',
            'type'=>Field::TYPE_BOOL,
            'subtype'=>'',
            'readonly'=>true
        ),
        'f_seen' => array(
            'title'=>'Seen',
            'type'=>Field::TYPE_BOOL,
            'subtype'=>'',
            'readonly'=>true,
            "default"=>array(
                "value"=>'f',
                "on"=>"null"
            )
        ),
        'campaign_id' => array(
            'title'=>'Campaign',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'marketing_campaign'
        ),
        'queue_id' => array(
            'title'=>'Queue',
            'type'=>Field::TYPE_GROUPING,
            'subtype'=>'object_groupings',
        ),
        'source_id' => array(
            'title'=>'Source',
            'type'=>Field::TYPE_GROUPING,
            'subtype'=>'object_groupings',
        ),
        'rating_id' => array(
            'title'=>'Rating',
            'type'=>Field::TYPE_GROUPING,
            'subtype'=>'object_groupings',
        ),
        'status_id' => array(
            'title'=>'Status',
            'type'=>Field::TYPE_GROUPING,
            'subtype'=>'object_groupings',
        ),
        'class_id' => array(
            'title'=>'Class',
            'type'=>Field::TYPE_GROUPING,
            'subtype'=>'object_groupings',
        ),
        'converted_opportunity_id' => array(
            'title'=>'Opportunity',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'opportunity'
        ),
        'converted_customer_id' => array(
            'title'=>'Customer',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'customer'
        ),
        'ts_converted' => array(
            'title'=>'Date Converted',
            'type'=>Field::TYPE_TIMESTAMP,
            'subtype'=>'',
            'readonly'=>true
        ),
    ),
);
