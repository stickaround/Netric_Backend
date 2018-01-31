<?php
namespace data\entity_definitions;
use Netric\EntityDefinition\Field;

return array(
    'is_private' => true,
    'default_activity_level' => 1,
    'fields' => array(
        'name' => array(
            'title'=>'Name',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'512',
            'readonly'=>true,
            'default'=>array(
                "value"=>"Untitled",
                "on"=>"update",
                "coalesce"=>array(
                    array(
                        "first_name",
                        "last_name"
                    ),
                    "company",
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
        'middle_name' => array(
            'title'=>'Middle Name',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'64',
            'readonly'=>false
        ),
        'spouse_name' => array(
            'title'=>'Spouse Name',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'64',
            'readonly'=>false
        ),
        'company' => array(
            'title'=>'Company',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>false,
        ),
        'job_title' => array(
            'title'=>'Job Title',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'64',
            'readonly'=>false
        ),
        'salutation' => array(
            'title'=>'Salutation',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>false
        ),
        'email' => array(
            'title'=>'Email Home',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'email',
            'readonly'=>false
        ),
        'email2' => array(
            'title'=>'Email Work',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'email',
            'readonly'=>false
        ),
        'email_spouse' => array(
            'title'=>'Email Spouse',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'email',
            'readonly'=>false
        ),
        'phone_home' => array(
            'title'=>'Home Phone',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'64',
            'readonly'=>false
        ),
        'phone_work' => array(
            'title'=>'Work Phone',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'64',
            'readonly'=>false
        ),
        'phone_other' => array(
            'title'=>'Other Phone',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'32',
            'readonly'=>false
        ),
        'street' => array(
            'title'=>'Home Street',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'128',
            'readonly'=>false
        ),
        'street_2' => array(
            'title'=>'Home Street 2',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'128',
            'readonly'=>false
        ),
        'city' => array(
            'title'=>'Home City',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'128',
            'readonly'=>false
        ),
        'state' => array(
            'title'=>'Home State',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'64',
            'readonly'=>false
        ),
        'zip' => array(
            'title'=>'Home Zip',
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
        'phone_cell' => array(
            'title'=>'Mobile Phone',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'64',
            'readonly'=>false
        ),
        'phone_fax' => array(
            'title'=>'Fax',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'32',
            'readonly'=>false
        ),
        'phone_pager' => array(
            'title'=>'Pager',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'32',
            'readonly'=>false
        ),
        'website' => array(
            'title'=>'Website',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'128',
            'readonly'=>false
        ),
        'nick_name' => array(
            'title'=>'Nick Name',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>false
        ),
        'last_contacted' => array(
            'title'=>'Last Contacted',
            'type'=>Field::TYPE_TIMESTAMP,
            'subtype'=>'',
            'readonly'=>false
        ),
        'business_street' => array(
            'title'=>'Business Street',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'128',
            'readonly'=>false
        ),
        'business_street_2' => array(
            'title'=>'Business Street 2',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'128',
            'readonly'=>false
        ),
        'business_city' => array(
            'title'=>'Business City',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'128',
            'readonly'=>false
        ),
        'business_state' => array(
            'title'=>'Business State',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'64',
            'readonly'=>false
        ),
        'business_zip' => array(
            'title'=>'Business Zip',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'zipcode',
            'readonly'=>false
        ),
        'business_website' => array(
            'title'=>'Business Website',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'128',
            'readonly'=>false
        ),
        'birthday' => array(
            'title'=>'Birthday',
            'type'=>Field::TYPE_DATE,
            'subtype'=>'',
            'readonly'=>false
        ),
        'birthday_spouse' => array(
            'title'=>'Spouse Birthday',
            'type'=>Field::TYPE_DATE,
            'subtype'=>'',
            'readonly'=>false
        ),
        'anniversary' => array(
            'title'=>'Anniversary',
            'type'=>Field::TYPE_DATE,
            'subtype'=>'',
            'readonly'=>false
        ),
        'ext' => array(
            'title'=>'Ext.',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'32',
            'readonly'=>false
        ),
        'email_default' => array(
            'title'=>'Default Email',
            'type'=>Field::TYPE_ALIAS,
            'subtype'=>'email',
            'readonly'=>false,
            'default'=>array(
                "value"=>"email",
                "on"=>"null",
                "coalesce"=>array(
                    "email", "email2"
                ),
            ),
        ),
        'date_entered' => array(
            'title'=>'Date Entered',
            'type'=>Field::TYPE_DATE,
            'subtype'=>'',
            'readonly'=>true,
            'default'=>array(
                "value"=>"now", "on"=>"create"
            ),
        ),
        'date_changed' => array(
            'title'=>'Date Changed',
            'type'=>Field::TYPE_TIMESTAMP,
            'subtype'=>'',
            'readonly'=>true,
            'default'=>array(
                "value"=>"now", "on"=>"update"
            ),
        ),
        'user_id' => array(
            'title'=>'Owner',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'user',
            'default'=>array("value"=>"-3", "on"=>"null")
        ),
        'image_id' => array(
            'title'=>'Image',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'file',
        ),
        'groups' => array(
            'title'=>'Groups',
            'type'=>Field::TYPE_GROUPING_MULTI,
            'subtype'=>'object_groupings',
        ),
        'customer_id' => array(
            'title'=>'Customer',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'customer'
        ),
    ),
);
