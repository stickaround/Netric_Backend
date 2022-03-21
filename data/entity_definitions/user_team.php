<?php
namespace data\entity_definitions;

use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\Field;
use Netric\EntityDefinition\ObjectTypes;

return [
    'uname_settings' => 'name',
    "fields" => [
       'name' => [
            'title' => 'Username',
            'type' => Field::TYPE_TEXT,
            'subtype' => '128',
            'readonly' => false,
            'required' => true,
            'unique' => true,
        ],
         // Full name first + last
        'full_name' => [
            'title' => 'Full Name',
            'type' => Field::TYPE_TEXT,
            'subtype' => '128',
            'readonly' => false,
            'required' => true
        ],
        // We support different types of users
        'type' => [
            'title' => 'Type',
            'type' => Field::TYPE_TEXT,
            'subtype' => '16',
            'readonly' => true,
            'optional_values' => [
                // Users that can log in and are memers of the Users group
                UserEntity::TYPE_INTERNAL => "Internal User",
                // Public users are generally third parties - partners/customers
                UserEntity::TYPE_PUBLIC => "Public User",
                // API used by code to interact with netric - not a human
                UserEntity::TYPE_SYSTEM => "API / System",
                // Meta-users are users that point to actual users, like Creator/Owner
                UserEntity::TYPE_META => "Meta",
            ],
            "default" => ["value" => UserEntity::TYPE_INTERNAL, "on" => "null"]
        ],
        'theme' => [
            'title' => 'Theme',
            'type' => Field::TYPE_TEXT,
            'subtype' => '32',
        ],

        'timezone' => [
            'title' => 'Timezone',
            'type' => Field::TYPE_TEXT,
            'subtype' => '64',
        ],

        'notes' => [
            'title' => 'About',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
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
       
        'company' => [
            'title' => 'Organization Name',
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
        'email' => [
            'title' => 'Email',
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
        // 'customer_stage_id' => [
        //     'title' => 'Stage',
        //     'type' => Field::TYPE_GROUPING,
        //     'subtype' => 'object_groupings',
        // ],
        'source_id' => [
            'title' => 'Source',
            'type' => Field::TYPE_GROUPING,
            'subtype' => 'object_groupings',
        ],
        'active' => [
            'title' => 'Active',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => false,
            "default" => ["value" => true, "on" => "null"]
        ],
       // Tracks activity in netric
        "last_active" => [
            'title' => 'Last Active',
            'type' => Field::TYPE_TIMESTAMP,
            'subtype' => '',
            'readonly' => true,
        ],
        'last_login' => [
            'title' => 'Last Login',
            'type' => 'timestamp',
            'subtype' => '',
            'readonly' => true
        ],
        'image_id' => [
            'title' => 'Image',
            'type' => Field::TYPE_OBJECT,
            'subtype' => 'file'
        ],
        // The banner or hero image is used as a full-width image for the user profile
        'banner_image_id' => [
            'title' => 'Banner IMage',
            'type' => Field::TYPE_OBJECT,
            'subtype' => ObjectTypes::FILE,
        ],
        'team_id' => [
            'title' => 'Team',
            'type' => Field::TYPE_OBJECT,
            'subtype' => ObjectTypes::USER_TEAM,
        ],
        'groups' => [
            'title' => 'Groups',
            'type' => Field::TYPE_GROUPING_MULTI,
            'subtype' => 'object_groupings',
        ],
        'manager_id' => [
            'title' => 'Manager',
            'type' => Field::TYPE_OBJECT,
            'subtype' => 'user'
        ],
        // Every user has a contact where we store contact data like address etc
        'contact_id' => [
            'title' => "Contact",
            'type' => Field::TYPE_OBJECT,
            'subtype' => ObjectTypes::CONTACT,
        ]
    ],
];

/*return array(
    'is_private' => true,
    'default_activity_level' => 1,
    'store_revisions' => true,
    'fields' => array(
        'name' => array(
            'title'=>'Name',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>false,
            'require'=>true
        ),
        'parent_id' => array(
            'title'=>'Parent',
            'type'=>Field::TYPE_INTEGER,
            'readonly'=>false,
            'require'=>false
        ),
    ),
); */
