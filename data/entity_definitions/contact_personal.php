<?php
namespace data\entity_definitions;

return array(
    'is_private' => true,
    'default_activity_level' => 1,
    'fields' => array(
        'name' => array(
            'title'=>'Name',
            'type'=>'text',
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
        'middle_name' => array(
            'title'=>'Middle Name',
            'type'=>'text',
            'subtype'=>'64',
            'readonly'=>false
        ),
        'spouse_name' => array(
            'title'=>'Spouse Name',
            'type'=>'text',
            'subtype'=>'64',
            'readonly'=>false
        ),
        'company' => array(
            'title'=>'Company',
            'type'=>'text',
            'subtype'=>'256',
            'readonly'=>false,
        ),
        'job_title' => array(
            'title'=>'Job Title',
            'type'=>'text',
            'subtype'=>'64',
            'readonly'=>false
        ),
        'salutation' => array(
            'title'=>'Salutation',
            'type'=>'text',
            'subtype'=>'256',
            'readonly'=>false
        ),
        'email' => array(
            'title'=>'Email Home',
            'type'=>'text',
            'subtype'=>'email',
            'readonly'=>false
        ),
        'email2' => array(
            'title'=>'Email Work',
            'type'=>'text',
            'subtype'=>'email',
            'readonly'=>false
        ),
        'email_spouse' => array(
            'title'=>'Email Spouse',
            'type'=>'text',
            'subtype'=>'email',
            'readonly'=>false
        ),
        'phone_home' => array(
            'title'=>'Home Phone',
            'type'=>'text',
            'subtype'=>'64',
            'readonly'=>false
        ),
        'phone_work' => array(
            'title'=>'Work Phone',
            'type'=>'text',
            'subtype'=>'64',
            'readonly'=>false
        ),
        'phone_other' => array(
            'title'=>'Other Phone',
            'type'=>'text',
            'subtype'=>'32',
            'readonly'=>false
        ),
        'street' => array(
            'title'=>'Home Street',
            'type'=>'text',
            'subtype'=>'128',
            'readonly'=>false
        ),
        'street_2' => array(
            'title'=>'Home Street 2',
            'type'=>'text',
            'subtype'=>'128',
            'readonly'=>false
        ),
        'city' => array(
            'title'=>'Home City',
            'type'=>'text',
            'subtype'=>'128',
            'readonly'=>false
        ),
        'state' => array(
            'title'=>'Home State',
            'type'=>'text',
            'subtype'=>'64',
            'readonly'=>false
        ),
        'zip' => array(
            'title'=>'Home Zip',
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
        'phone_cell' => array(
            'title'=>'Mobile Phone',
            'type'=>'text',
            'subtype'=>'64',
            'readonly'=>false
        ),
        'phone_fax' => array(
            'title'=>'Fax',
            'type'=>'text',
            'subtype'=>'32',
            'readonly'=>false
        ),
        'phone_pager' => array(
            'title'=>'Pager',
            'type'=>'text',
            'subtype'=>'32',
            'readonly'=>false
        ),
        'website' => array(
            'title'=>'Website',
            'type'=>'text',
            'subtype'=>'128', '
            readonly'=>false
        ),
        'nick_name' => array(
            'title'=>'Nick Name',
            'type'=>'text',
            'subtype'=>'256',
            'readonly'=>false
        ),
        'last_contacted' => array(
            'title'=>'Last Contacted',
            'type'=>'timestamp',
            'subtype'=>'',
            'readonly'=>false
        ),
        'business_street' => array(
            'title'=>'Business Street',
            'type'=>'text',
            'subtype'=>'128',
            'readonly'=>false
        ),
        'business_street_2' => array(
            'title'=>'Business Street 2',
            'type'=>'text',
            'subtype'=>'128',
            'readonly'=>false
        ),
        'business_city' => array(
            'title'=>'Business City',
            'type'=>'text',
            'subtype'=>'128',
            'readonly'=>false
        ),
        'business_state' => array(
            'title'=>'Business State',
            'type'=>'text',
            'subtype'=>'64',
            'readonly'=>false
        ),
        'business_zip' => array(
            'title'=>'Business Zip',
            'type'=>'text',
            'subtype'=>'zipcode',
            'readonly'=>false
        ),
        'business_website' => array(
            'title'=>'Business Website',
            'type'=>'text',
            'subtype'=>'128',
            'readonly'=>false
        ),
        'birthday' => array(
            'title'=>'Birthday',
            'type'=>'date',
            'subtype'=>'',
            'readonly'=>false
        ),
        'birthday_spouse' => array(
            'title'=>'Spouse Birthday',
            'type'=>'date',
            'subtype'=>'',
            'readonly'=>false
        ),
        'anniversary' => array(
            'title'=>'Anniversary',
            'type'=>'date',
            'subtype'=>'',
            'readonly'=>false
        ),
        'ext' => array(
            'title'=>'Ext.',
            'type'=>'text',
            'subtype'=>'32',
            'readonly'=>false
        ),
        'email_default' => array(
            'title'=>'Default Email',
            'type'=>'alias',
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
            'type'=>'date',
            'subtype'=>'',
            'readonly'=>true,
            'default'=>array(
                "value"=>"now", "on"=>"create"
            ),
        ),
        'date_changed' => array(
            'title'=>'Date Changed',
            'type'=>'timestamp',
            'subtype'=>'',
            'readonly'=>true,
            'default'=>array(
                "value"=>"now", "on"=>"update"
            ),
        ),
        'user_id' => array(
            'title'=>'Owner',
            'type'=>'object',
            'subtype'=>'user',
            'default'=>array("value"=>"-3", "on"=>"null")
        ),
        'image_id' => array(
            'title'=>'Image',
            'type'=>'object',
            'subtype'=>'file',
        ),
        'groups' => array(
            'title'=>'Groups',
            'type'=>'fkey_multi',
            'subtype'=>'object_groupings',
            'fkey_table'=>array(
                "key"=>"id",
                "title"=>"name",
                "parent"=>"parent_id",
                "filter"=>array("user_id"=>"user_id"),
                "ref_table"=>array(
                    "table"=>"object_grouping_mem",
                    "this"=>"object_id",
                    "ref"=>"grouping_id"
                ),
            ),
        ),
        'customer_id' => array(
            'title'=>'Customer',
            'type'=>'object',
            'subtype'=>'customer'
        ),
    ),
);
