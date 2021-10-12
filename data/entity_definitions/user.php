<?php

namespace data\entity_definitions;

use Netric\EntityDefinition\Field;

return [
    'uname_settings' => 'name',
    'fields' => [
        // User name
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

        'password' => [
            'title' => 'Password',
            'type' => Field::TYPE_TEXT,
            'subtype' => 'password',
            'readonly' => false
        ],

        'password_salt' => [
            'title' => 'Password Salt',
            'type' => Field::TYPE_TEXT,
            'subtype' => 'password',
            'readonly' => true
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

        'email' => [
            'title' => 'Email',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false,
            'required' => false
        ],

        'phone_office' => [
            'title' => 'Office Phone',
            'type' => Field::TYPE_TEXT,
            'subtype' => '64',
            'readonly' => false,
            'mask' => 'phone_dash'
        ],

        'phone_ext' => [
            'title' => 'Office Phone Ext.',
            'type' => Field::TYPE_TEXT,
            'subtype' => '16',
            'readonly' => false
        ],

        'phone_mobile' => [
            'title' => 'Mobile Phone',
            'type' => Field::TYPE_TEXT,
            'subtype' => '64',
            'readonly' => false,
            'mask' => 'phone_dash'
        ],

        'phone_mobile_carrier' => [
            'title' => 'Mobile Carrier',
            'type' => Field::TYPE_TEXT,
            'subtype' => '64',
            'readonly' => false,
            'mask' => 'phone_dash',
            'optional_values' => [
                "" => "None",
                "@vtext.com" => "Verizon Wireless",
                "@messaging.sprintpcs.com" => "Sprint/Nextel",
                "@txt.att.net" => "AT&T Wireless",
                "@tmomail.net" => "T Mobile",
                "@cingularme.com" => "Cingular Wireless",
                "@mobile.surewest.com" => "SureWest",
                "@mymetropcs.com" => "Metro PCS",
            ],
        ],

        'phone_home' => [
            'title' => 'Home Phone',
            'type' => Field::TYPE_TEXT,
            'subtype' => '64',
            'readonly' => false,
            'mask' => 'phone_dash'
        ],

        // Aereus customer number
        'customer_number' => [
            'title' => 'Netric Customer Number',
            'type' => Field::TYPE_TEXT,
            'subtype' => '64',
            'readonly' => true,
        ],

        'job_title' => [
            'title' => 'Job Title',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false,
            'required' => false
        ],
        'city' => [
            'title' => 'City',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false,
            'required' => false
        ],
        // Used to indicate the presence of a user
        'presence' => [
            'title' => 'Presence',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false,
            'required' => false,
            'optional_values' => [
                "online" => "Online",
                "idle" => "Idle",
                "offline" => "Offline",
            ],
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
        'team_id' => [
            'title' => 'Team',
            'type' => Field::TYPE_OBJECT,
            'subtype' => 'user_team',
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
    ],
];
