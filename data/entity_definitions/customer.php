<?php
namespace data\entity_definitions;
use Netric\EntityDefinition\Field;

return array(
    'uname_settings' => 'name',
    "fields" => array(
        'name' => array(
            'title'=>'Name',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'512',
            'readonly'=>false,
            'default'=>array(
                "value"=>"New Contact",
                "on"=>"null",
                "coalesce"=>array(
                    array("first_name", "last_name"), "company"
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
            'readonly'=>false
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
        'email3' => array(
            'title'=>'Email Other',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'email',
            'readonly'=>false
        ),
        'phone_home' => array(
            'title'=>'Home Phone',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'phone',
            'readonly'=>false
        ),
        'phone_work' => array(
            'title'=>'Work Phone',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'phone',
            'readonly'=>false
        ),
        'phone_cell' => array(
            'title'=>'Mobile Phone',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'phone',
            'readonly'=>false
        ),
        'phone_ext' => array(
            'title'=>'Ext.',
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
        'street2' => array(
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
        'phone_fax' => array(
            'title'=>'Fax',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'64',
            'readonly'=>false
        ),
        'phone_pager' => array(
            'title'=>'Pager',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'64',
            'readonly'=>false
        ),
        'business_street' => array(
            'title'=>'Business Street',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'128',
            'readonly'=>false
        ),
        'business_street2' => array(
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
        'shipping_street' => array(
            'title'=>'Shipping Street',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>false
        ),
        'shipping_street2' => array(
            'title'=>'Shipping Street 2',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>false
        ),
        'shipping_city' => array(
            'title'=>'Shipping City',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'128',
            'readonly'=>false
        ),
        'shipping_state' => array(
            'title'=>'Shipping State',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'64',
            'readonly'=>false
        ),
        'shipping_zip' => array(
            'title'=>'Shipping Zip',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'zipcode',
            'readonly'=>false
        ),
        'billing_street' => array(
            'title'=>'Billing Street',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>false
        ),
        'billing_street2' => array(
            'title'=>'Billing Street 2',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>false
        ),
        'billing_city' => array(
            'title'=>'Billing City',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'128',
            'readonly'=>false
        ),
        'billing_state' => array(
            'title'=>'Billing State',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'64',
            'readonly'=>false
        ),
        'billing_zip' => array(
            'title'=>'Billing Zip',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'zipcode',
            'readonly'=>false
        ),
        'website' => array(
            'title'=>'Website',
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
        'last_contacted' => array(
            'title'=>'Last Contacted',
            'type'=>Field::TYPE_TIMESTAMP,
            'subtype'=>'',
            'readonly'=>false
        ),
        'nick_name' => array(
            'title'=>'Nick Name',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>false
        ),
        'email_spouse' => array(
            'title'=>'Email Spouse',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'email',
            'readonly'=>false
        ),
        'notes' => array(
            'title'=>'Notes',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>false
        ),
        'f_nocall' => array(
            'title'=>'Do Not Call',
            'type'=>Field::TYPE_BOOL,
            'subtype'=>'',
            'readonly'=>false
        ),
        'f_noemailspam' => array(
            'title'=>'No Bulk Email',
            'type'=>Field::TYPE_BOOL,
            'subtype'=>'',
            'readonly'=>false
        ),
        'f_nocontact' => array(
            'title'=>'Do Not Contact',
            'type'=>Field::TYPE_BOOL,
            'subtype'=>'',
            'readonly'=>false
        ),
        'f_emailverified' => array(
            'title'=>'Email Verified',
            'type'=>Field::TYPE_BOOL,
            'subtype'=>'',
            'readonly'=>false
        ),
        'f_private' => array(
            'title'=>'Personal Contact',
            'type'=>Field::TYPE_BOOL,
            'subtype'=>'',
            'readonly'=>false,
            'default'=>array("value"=>"f", "on"=>"null"),
        ),
        'facebook' => array(
            'title'=>'Facebook URL',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>false
        ),
        'twitter' => array(
            'title'=>'Twitter User',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>false
        ),
        'linkedin' => array(
            'title'=>'LinkedIn Profile',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>false
        ),
        'type_id' => array(
            'title'=>'Type',
            'type'=>Field::TYPE_INTEGER,
            'subtype'=>'',
            'optional_values'=>array("2"=>"Organization", "1"=>"Person")
        ),
        'status_id' => array(
            'title'=>'Status',
            'type'=>Field::TYPE_GROUPING,
            'subtype'=>'object_groupings',
            'fkey_table'=>array("key"=>"id", "title"=>"name")
        ),
        'primary_contact' => array(
            'title'=>'Primary Contact',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'customer'
        ),
        'primary_account' => array(
            'title'=>'Organization',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'customer'
        ),
        'stage_id' => array(
            'title'=>'Stage',
            'type'=>Field::TYPE_GROUPING,
            'subtype'=>'object_groupings',
            'fkey_table'=>array("key"=>"id", "title"=>"name")
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
                )
            ),
        ),
        'address_default' => array(
            'title'=>'Default Address',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'128',
            'readonly'=>false,
            'optional_values'=>array(
                "home"=>"Home", "business"=>"Business"
            ),
        ),
        'time_entered' => array(
            'title'=>'Time Entered',
            'type'=>Field::TYPE_TIMESTAMP,
            'subtype'=>'',
            'readonly'=>true,
            'default'=>array("value"=>"now", "on"=>"create")
        ),
        'time_changed' => array(
            'title'=>'Time Changed',
            'type'=>Field::TYPE_TIMESTAMP,
            'subtype'=>'',
            'readonly'=>true,
            'default'=>array("value"=>"now", "on"=>"update")
        ),
        'owner_id' => array(
            'title'=>'Owner',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'user',
            'default'=>array("value"=>"-3", "on"=>"null")
        ),
        'groups' => array(
            'title'=>'Groups',
            'type'=>Field::TYPE_GROUPING_MULTI,
            'subtype'=>'object_groupings',
            'fkey_table'=>array(
                "key"=>"id",
                "title"=>"name",
                "parent"=>"parent_id",
                "ref_table"=>array(
                    "table"=>"object_grouping_mem",
                    "this"=>"object_id",
                    "ref"=>"grouping_id"
                ),
            ),
        ),
        'folder_id' => array(
            'title'=>'Files',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'folder',
            'autocreate'=>true, // Create foreign object automatically
            'autocreatebase'=>'/System/Customer Files', // Where to create (for folders, the path with no trail slash)
            'autocreatename'=>'id', // the field to pull the new object name from
            'fkey_table'=>array("key"=>"id", "title"=>"name")
        ),
        'image_id' => array(
            'title'=>'Image',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'file',
            'fkey_table'=>array("key"=>"id", "title"=>"file_title")
        ),
    ),
);
