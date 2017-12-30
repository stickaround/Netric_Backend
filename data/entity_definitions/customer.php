<?php
namespace data\entity_definitions;

return array(
    'uname_settings' => 'name',
    "fields" => array(
        'name' => array(
            'title'=>'Name',
            'type'=>'text',
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
            'readonly'=>false
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
        'email3' => array(
            'title'=>'Email Other',
            'type'=>'text',
            'subtype'=>'email',
            'readonly'=>false
        ),
        'phone_home' => array(
            'title'=>'Home Phone',
            'type'=>'text',
            'subtype'=>'phone',
            'readonly'=>false
        ),
        'phone_work' => array(
            'title'=>'Work Phone',
            'type'=>'text',
            'subtype'=>'phone',
            'readonly'=>false
        ),
        'phone_cell' => array(
            'title'=>'Mobile Phone',
            'type'=>'text',
            'subtype'=>'phone',
            'readonly'=>false
        ),
        'phone_ext' => array(
            'title'=>'Ext.',
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
        'street2' => array(
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
        'phone_fax' => array(
            'title'=>'Fax',
            'type'=>'text',
            'subtype'=>'64',
            'readonly'=>false
        ),
        'phone_pager' => array(
            'title'=>'Pager',
            'type'=>'text',
            'subtype'=>'64',
            'readonly'=>false
        ),
        'business_street' => array(
            'title'=>'Business Street',
            'type'=>'text',
            'subtype'=>'128',
            'readonly'=>false
        ),
        'business_street2' => array(
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
        'shipping_street' => array(
            'title'=>'Shipping Street',
            'type'=>'text',
            'subtype'=>'256',
            'readonly'=>false
        ),
        'shipping_street2' => array(
            'title'=>'Shipping Street 2',
            'type'=>'text',
            'subtype'=>'256',
            'readonly'=>false
        ),
        'shipping_city' => array(
            'title'=>'Shipping City',
            'type'=>'text',
            'subtype'=>'128',
            'readonly'=>false
        ),
        'shipping_state' => array(
            'title'=>'Shipping State',
            'type'=>'text',
            'subtype'=>'64',
            'readonly'=>false
        ),
        'shipping_zip' => array(
            'title'=>'Shipping Zip',
            'type'=>'text',
            'subtype'=>'zipcode',
            'readonly'=>false
        ),
        'billing_street' => array(
            'title'=>'Billing Street',
            'type'=>'text',
            'subtype'=>'256',
            'readonly'=>false
        ),
        'billing_street2' => array(
            'title'=>'Billing Street 2',
            'type'=>'text',
            'subtype'=>'256',
            'readonly'=>false
        ),
        'billing_city' => array(
            'title'=>'Billing City',
            'type'=>'text',
            'subtype'=>'128',
            'readonly'=>false
        ),
        'billing_state' => array(
            'title'=>'Billing State',
            'type'=>'text',
            'subtype'=>'64',
            'readonly'=>false
        ),
        'billing_zip' => array(
            'title'=>'Billing Zip',
            'type'=>'text',
            'subtype'=>'zipcode',
            'readonly'=>false
        ),
        'website' => array(
            'title'=>'Website',
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
        'last_contacted' => array(
            'title'=>'Last Contacted',
            'type'=>'timestamp',
            'subtype'=>'',
            'readonly'=>false
        ),
        'nick_name' => array(
            'title'=>'Nick Name',
            'type'=>'text',
            'subtype'=>'256',
            'readonly'=>false
        ),
        'email_spouse' => array(
            'title'=>'Email Spouse',
            'type'=>'text',
            'subtype'=>'email',
            'readonly'=>false
        ),
        'notes' => array(
            'title'=>'Notes',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>false
        ),
        'f_nocall' => array(
            'title'=>'Do Not Call',
            'type'=>'bool',
            'subtype'=>'',
            'readonly'=>false
        ),
        'f_noemailspam' => array(
            'title'=>'No Bulk Email',
            'type'=>'bool',
            'subtype'=>'',
            'readonly'=>false
        ),
        'f_nocontact' => array(
            'title'=>'Do Not Contact',
            'type'=>'bool',
            'subtype'=>'',
            'readonly'=>false
        ),
        'f_emailverified' => array(
            'title'=>'Email Verified',
            'type'=>'bool',
            'subtype'=>'',
            'readonly'=>false
        ),
        'f_private' => array(
            'title'=>'Personal Contact',
            'type'=>'bool',
            'subtype'=>'',
            'readonly'=>false,
            'default'=>array("value"=>"f", "on"=>"null"),
        ),
        'facebook' => array(
            'title'=>'Facebook URL',
            'type'=>'text',
            'subtype'=>'256',
            'readonly'=>false
        ),
        'twitter' => array(
            'title'=>'Twitter User',
            'type'=>'text',
            'subtype'=>'256',
            'readonly'=>false
        ),
        'linkedin' => array(
            'title'=>'LinkedIn Profile',
            'type'=>'text',
            'subtype'=>'256',
            'readonly'=>false
        ),
        'type_id' => array(
            'title'=>'Type',
            'type'=>'integer',
            'subtype'=>'',
            'optional_values'=>array("2"=>"Organization", "1"=>"Person")
        ),
        'status_id' => array(
            'title'=>'Status',
            'type'=>'fkey',
            'subtype'=>'customer_status',
            'fkey_table'=>array("key"=>"id", "title"=>"name")
        ),
        'primary_contact' => array(
            'title'=>'Primary Contact',
            'type'=>'object',
            'subtype'=>'customer'
        ),
        'primary_account' => array(
            'title'=>'Organization',
            'type'=>'object',
            'subtype'=>'customer'
        ),
        'stage_id' => array(
            'title'=>'Stage',
            'type'=>'fkey',
            'subtype'=>'customer_stages',
            'fkey_table'=>array("key"=>"id", "title"=>"name")
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
                )
            ),
        ),
        'address_default' => array(
            'title'=>'Default Address',
            'type'=>'text',
            'subtype'=>'128',
            'readonly'=>false,
            'optional_values'=>array(
                "home"=>"Home", "business"=>"Business"
            ),
        ),
        'time_entered' => array(
            'title'=>'Time Entered',
            'type'=>'timestamp',
            'subtype'=>'',
            'readonly'=>true,
            'default'=>array("value"=>"now", "on"=>"create")
        ),
        'time_changed' => array(
            'title'=>'Time Changed',
            'type'=>'timestamp',
            'subtype'=>'',
            'readonly'=>true,
            'default'=>array("value"=>"now", "on"=>"update")
        ),
        'owner_id' => array(
            'title'=>'Owner',
            'type'=>'object',
            'subtype'=>'user',
            'default'=>array("value"=>"-3", "on"=>"null")
        ),
        'groups' => array(
            'title'=>'Groups',
            'type'=>'fkey_multi',
            'subtype'=>'customer_labels',
            'fkey_table'=>array("key"=>"id", "title"=>"name", "parent"=>"parent_id",
                "ref_table"=>array(
                    "table"=>"customer_label_mem",
                    "this"=>"customer_id",
                    "ref"=>"label_id"
                ),
            ),
        ),
        'folder_id' => array(
            'title'=>'Files',
            'type'=>'object',
            'subtype'=>'folder',
            'autocreate'=>true, // Create foreign object automatically
            'autocreatebase'=>'/System/Customer Files', // Where to create (for folders, the path with no trail slash)
            'autocreatename'=>'id', // the field to pull the new object name from
            'fkey_table'=>array("key"=>"id", "title"=>"name")
        ),
        'image_id' => array(
            'title'=>'Image',
            'type'=>'object',
            'subtype'=>'file',
            'fkey_table'=>array("key"=>"id", "title"=>"file_title")
        ),
    ),
);
