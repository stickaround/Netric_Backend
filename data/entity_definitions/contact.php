<?php

namespace data\entity_definitions;

use Netric\EntityDefinition\Field;
use Netric\EntityDefinition\ObjectTypes;

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
            'title' => 'Company Name',
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
            'title' => 'Email',
            'type' => Field::TYPE_TEXT,
            'subtype' => 'email',
            'readonly' => false
        ],
        'email2' => [
            'title' => 'Email 2',
            'type' => Field::TYPE_TEXT,
            'subtype' => 'email',
            'readonly' => false
        ],
        'email3' => [
            'title' => 'Email 3',
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
        'phone_fax' => [
            'title' => 'Fax Number',
            'type' => Field::TYPE_TEXT,
            'subtype' => '64',
            'readonly' => false
        ],
        'street' => [
            'title' => 'Street',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false
        ],
        'street2' => [
            'title' => 'Street 2',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false
        ],
        'city' => [
            'title' => 'City',
            'type' => Field::TYPE_TEXT,
            'subtype' => '128',
            'readonly' => false
        ],
        'district' => [
            'title' => 'State',
            'type' => Field::TYPE_TEXT,
            'subtype' => '128',
            'readonly' => false
        ],
        'postal_code' => [
            'title' => 'Zip',
            'type' => Field::TYPE_TEXT,
            'subtype' => 'zipcode',
            'readonly' => false
        ],
        // Right now we create this default billing address so that
        // new payment profiles have a default to work with. For simplicity,
        // we might remove this and just edit the payment profile address directly.
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
        'billing_district' => [
            'title' => 'Billing State',
            'type' => Field::TYPE_TEXT,
            'subtype' => '64',
            'readonly' => false
        ],
        'billing_postal_code' => [
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
        'notes' => [
            'title' => 'Notes',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => false
        ],
        'is_nocall' => [
            'title' => 'Do Not Call',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => false
        ],
        'is_noemailspam' => [
            'title' => 'No Bulk Email',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => false
        ],
        'is_nocontact' => [
            'title' => 'Do Not Contact',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => false
        ],
        'is_emailverified' => [
            'title' => 'Email Verified',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => false
        ],
        'is_private' => [
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
            'optional_values' => ["2" => "Organization", "1" => "Person"],
            'default' => [
                "value" => 1,
                "on" => "null",
            ],
        ],
        'status_id' => [
            'title' => 'Status',
            'type' => Field::TYPE_GROUPING,
            'subtype' => 'object_groupings',
        ],
        'primary_contact' => [
            'title' => 'Primary Contact',
            'type' => Field::TYPE_OBJECT,
            'subtype' => ObjectTypes::CONTACT,
        ],
        'primary_account' => [
            'title' => 'Organization',
            'type' => Field::TYPE_OBJECT,
            'subtype' => ObjectTypes::CONTACT,
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
        'groups' => [
            'title' => 'Groups',
            'type' => Field::TYPE_GROUPING_MULTI,
            'subtype' => 'object_groupings',
        ],
        'image_id' => [
            'title' => 'Image',
            'type' => Field::TYPE_OBJECT,
            'subtype' => 'file'
        ],
    ],
];
