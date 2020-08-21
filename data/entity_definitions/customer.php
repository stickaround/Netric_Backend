<?php

namespace data\entity_definitions;

use Netric\EntityDefinition\Field;

return [
    "fields" => [
        'name' => [
            'title' => 'Name',
            'type' => Field::TYPE_TEXT,
            'subtype' => '512',
            'readonly' => false,
            'default' => [
                "value" => "New Contact",
                "on" => "null",
                "coalesce" => [
                    ["first_name", "last_name"], "company"
                ],
            ],
        ],
        'first_name' => [
            'title' => 'First Name',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false
        ],
        'last_name' => [
            'title' => 'Last Name',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false
        ],
        'middle_name' => [
            'title' => 'Middle Name',
            'type' => Field::TYPE_TEXT,
            'subtype' => '64',
            'readonly' => false
        ],
        'spouse_name' => [
            'title' => 'Spouse Name',
            'type' => Field::TYPE_TEXT,
            'subtype' => '64',
            'readonly' => false
        ],
        'company' => [
            'title' => 'Company',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false
        ],
        'job_title' => [
            'title' => 'Job Title',
            'type' => Field::TYPE_TEXT,
            'subtype' => '64',
            'readonly' => false
        ],
        'salutation' => [
            'title' => 'Salutation',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false
        ],
        'email' => [
            'title' => 'Email Home',
            'type' => Field::TYPE_TEXT,
            'subtype' => 'email',
            'readonly' => false
        ],
        'email2' => [
            'title' => 'Email Work',
            'type' => Field::TYPE_TEXT,
            'subtype' => 'email',
            'readonly' => false
        ],
        'email3' => [
            'title' => 'Email Other',
            'type' => Field::TYPE_TEXT,
            'subtype' => 'email',
            'readonly' => false
        ],
        'phone_home' => [
            'title' => 'Home Phone',
            'type' => Field::TYPE_TEXT,
            'subtype' => 'phone',
            'readonly' => false
        ],
        'phone_work' => [
            'title' => 'Work Phone',
            'type' => Field::TYPE_TEXT,
            'subtype' => 'phone',
            'readonly' => false
        ],
        'phone_cell' => [
            'title' => 'Mobile Phone',
            'type' => Field::TYPE_TEXT,
            'subtype' => 'phone',
            'readonly' => false
        ],
        'phone_ext' => [
            'title' => 'Ext.',
            'type' => Field::TYPE_TEXT,
            'subtype' => '32',
            'readonly' => false
        ],
        'street' => [
            'title' => 'Home Street',
            'type' => Field::TYPE_TEXT,
            'subtype' => '128',
            'readonly' => false
        ],
        'street2' => [
            'title' => 'Home Street 2',
            'type' => Field::TYPE_TEXT,
            'subtype' => '128',
            'readonly' => false
        ],
        'city' => [
            'title' => 'Home City',
            'type' => Field::TYPE_TEXT,
            'subtype' => '128',
            'readonly' => false
        ],
        'state' => [
            'title' => 'Home State',
            'type' => Field::TYPE_TEXT,
            'subtype' => '64',
            'readonly' => false
        ],
        'zip' => [
            'title' => 'Home Zip',
            'type' => Field::TYPE_TEXT,
            'subtype' => 'zipcode',
            'readonly' => false
        ],
        'phone_fax' => [
            'title' => 'Fax Number',
            'type' => Field::TYPE_TEXT,
            'subtype' => '64',
            'readonly' => false
        ],
        'phone_pager' => [
            'title' => 'Pager',
            'type' => Field::TYPE_TEXT,
            'subtype' => '64',
            'readonly' => false
        ],
        'business_street' => [
            'title' => 'Business Street',
            'type' => Field::TYPE_TEXT,
            'subtype' => '128',
            'readonly' => false
        ],
        'business_street2' => [
            'title' => 'Business Street 2',
            'type' => Field::TYPE_TEXT,
            'subtype' => '128',
            'readonly' => false
        ],
        'business_city' => [
            'title' => 'Business City',
            'type' => Field::TYPE_TEXT,
            'subtype' => '128',
            'readonly' => false
        ],
        'business_state' => [
            'title' => 'Business State',
            'type' => Field::TYPE_TEXT,
            'subtype' => '64',
            'readonly' => false
        ],
        'business_zip' => [
            'title' => 'Business Zip',
            'type' => Field::TYPE_TEXT,
            'subtype' => 'zipcode',
            'readonly' => false
        ],
        'shipping_street' => [
            'title' => 'Shipping Street',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false
        ],
        'shipping_street2' => [
            'title' => 'Shipping Street 2',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false
        ],
        'shipping_city' => [
            'title' => 'Shipping City',
            'type' => Field::TYPE_TEXT,
            'subtype' => '128',
            'readonly' => false
        ],
        'shipping_state' => [
            'title' => 'Shipping State',
            'type' => Field::TYPE_TEXT,
            'subtype' => '64',
            'readonly' => false
        ],
        // Used for type=organization only
        'billing_first_name' => [
            'title' => 'First Name',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false
        ],
        // Used for type=organization only
        'billing_last_name' => [
            'title' => 'Last Name',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false
        ],
        'shipping_zip' => [
            'title' => 'Shipping Zip',
            'type' => Field::TYPE_TEXT,
            'subtype' => 'zipcode',
            'readonly' => false
        ],
        'billing_street' => [
            'title' => 'Billing Street',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false
        ],
        'billing_street2' => [
            'title' => 'Billing Street 2',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false
        ],
        'billing_city' => [
            'title' => 'Billing City',
            'type' => Field::TYPE_TEXT,
            'subtype' => '128',
            'readonly' => false
        ],
        'billing_state' => [
            'title' => 'Billing State',
            'type' => Field::TYPE_TEXT,
            'subtype' => '64',
            'readonly' => false
        ],
        'billing_zip' => [
            'title' => 'Billing Zip',
            'type' => Field::TYPE_TEXT,
            'subtype' => 'zipcode',
            'readonly' => false
        ],
        'website' => [
            'title' => 'Website',
            'type' => Field::TYPE_TEXT,
            'subtype' => '128',
            'readonly' => false
        ],
        'birthday' => [
            'title' => 'Birthday',
            'type' => Field::TYPE_DATE,
            'subtype' => '',
            'readonly' => false
        ],
        'birthday_spouse' => [
            'title' => 'Spouse Birthday',
            'type' => Field::TYPE_DATE,
            'subtype' => '',
            'readonly' => false
        ],
        'anniversary' => [
            'title' => 'Anniversary',
            'type' => Field::TYPE_DATE,
            'subtype' => '',
            'readonly' => false
        ],
        'last_contacted' => [
            'title' => 'Last Contacted',
            'type' => Field::TYPE_TIMESTAMP,
            'subtype' => '',
            'readonly' => false
        ],
        'nick_name' => [
            'title' => 'Nick Name',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false
        ],
        'email_spouse' => [
            'title' => 'Email Spouse',
            'type' => Field::TYPE_TEXT,
            'subtype' => 'email',
            'readonly' => false
        ],
        'notes' => [
            'title' => 'Notes',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => false
        ],
        'f_nocall' => [
            'title' => 'Do Not Call',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => false
        ],
        'f_noemailspam' => [
            'title' => 'No Bulk Email',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => false
        ],
        'f_nocontact' => [
            'title' => 'Do Not Contact',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => false
        ],
        'f_emailverified' => [
            'title' => 'Email Verified',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => false
        ],
        'f_private' => [
            'title' => 'Personal Contact',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => false,
            'default' => ["value" => "f", "on" => "null"],
        ],
        'facebook' => [
            'title' => 'Facebook URL',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false
        ],
        'twitter' => [
            'title' => 'Twitter User',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false
        ],
        'linkedin' => [
            'title' => 'LinkedIn Profile',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false
        ],
        'type_id' => [
            'title' => 'Type',
            'type' => Field::TYPE_INTEGER,
            'subtype' => '',
            'optional_values' => ["2" => "Organization", "1" => "Person"]
        ],
        'status_id' => [
            'title' => 'Status',
            'type' => Field::TYPE_GROUPING,
            'subtype' => 'object_groupings',
        ],
        'primary_contact' => [
            'title' => 'Primary Contact',
            'type' => Field::TYPE_OBJECT,
            'subtype' => 'customer'
        ],
        'primary_account' => [
            'title' => 'Organization',
            'type' => Field::TYPE_OBJECT,
            'subtype' => 'customer'
        ],
        'stage_id' => [
            'title' => 'Stage',
            'type' => Field::TYPE_GROUPING,
            'subtype' => 'object_groupings',
        ],
        'email_default' => [
            'title' => 'Default Email',
            'type' => Field::TYPE_ALIAS,
            'subtype' => 'email',
            'readonly' => false,
            'default' => [
                "value" => "email",
                "on" => "null",
                "coalesce" => [
                    "email", "email2"
                ]
            ],
        ],
        'address_default' => [
            'title' => 'Default Address',
            'type' => Field::TYPE_TEXT,
            'subtype' => '128',
            'readonly' => false,
            'optional_values' => [
                "home" => "Home", "business" => "Business"
            ],
        ],
        'time_entered' => [
            'title' => 'Time Entered',
            'type' => Field::TYPE_TIMESTAMP,
            'subtype' => '',
            'readonly' => true,
            'default' => ["value" => "now", "on" => "create"]
        ],
        'time_changed' => [
            'title' => 'Time Changed',
            'type' => Field::TYPE_TIMESTAMP,
            'subtype' => '',
            'readonly' => true,
            'default' => ["value" => "now", "on" => "update"]
        ],
        'groups' => [
            'title' => 'Groups',
            'type' => Field::TYPE_GROUPING_MULTI,
            'subtype' => 'object_groupings',
        ],
        'folder_id' => [
            'title' => 'Files',
            'type' => Field::TYPE_OBJECT,
            'subtype' => 'folder',
            'autocreate' => true, // Create foreign object automatically
            'autocreatebase' => '/System/Customer Files', // Where to create (for folders, the path with no trail slash]
            'autocreatename' => 'id', // the field to pull the new object name from
            'fkey_table' => ["key" => "id", "title" => "name"]
        ],
        'image_id' => [
            'title' => 'Image',
            'type' => Field::TYPE_OBJECT,
            'subtype' => 'file',
            'fkey_table' => ["key" => "id", "title" => "file_title"]
        ],
    ],
];
