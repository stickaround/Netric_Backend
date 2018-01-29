<?php
namespace data\entity_definitions;
use Netric\EntityDefinition\Field;

return array(
    'fields' => array(
        // User name
        'name' => array(
            'title'=>'User Name',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'128',
            'readonly'=>false,
            'required'=>true,
            'unique'=>true,
        ),

        // Full name first + last
        'full_name' => array(
            'title'=>'Full Name',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'128',
            'readonly'=>false,
            'required'=>true
        ),

        'password' => array(
            'title'=>'Password',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'password',
            'readonly'=>false
        ),

        'password_salt' => array(
            'title'=>'PW Salt',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'password',
            'readonly'=>true
        ),

        'theme' => array(
            'title'=>'Theme',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'32',
        ),

        'timezone' => array(
            'title'=>'Timezone',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'64',
        ),

        'notes' => array(
            'title'=>'About',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
        ),

        'email' => array(
            'title'=>'Email',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>false,
            'required'=>false
        ),

        'phone_office' => array(
            'title'=>'Office Phone',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'64',
            'readonly'=>false,
            'mask'=>'phone_dash'
        ),

        'phone_ext' => array(
            'title'=>'Phone Ext.',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'16',
            'readonly'=>false
        ),

        'phone_mobile' => array(
            'title'=>'Mobile Phone',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'64',
            'readonly'=>false,
            'mask'=>'phone_dash'
        ),

        'phone_mobile_carrier' => array(
            'title'=>'Mobile Carrier',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'64',
            'readonly'=>false,
            'mask'=>'phone_dash',
            'optional_values'=>array(
                ""=>"None",
                "@vtext.com"=>"Verizon Wireless",
                "@messaging.sprintpcs.com"=>"Sprint/Nextel",
                "@txt.att.net"=>"AT&T Wireless",
                "@tmomail.net"=>"T Mobile",
                "@cingularme.com"=>"Cingular Wireless",
                "@mobile.surewest.com"=>"SureWest",
                "@mymetropcs.com"=>"Metro PCS",
            ),
        ),

        'phone_home' => array(
            'title'=>'Home Phone',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'64',
            'readonly'=>false,
            'mask'=>'phone_dash'
        ),

        // Aereus customer number
        'customer_number' => array(
            'title'=>'Netric Customer Number',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'64',
            'readonly'=>true,
        ),

        'job_title' => array(
            'title'=>'Job Title',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>false,
            'required'=>false
        ),
        'city' => array(
            'title'=>'City',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>false,
            'required'=>false
        ),
        'state' => array(
            'title'=>'State',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>false,
            'required'=>false
        ),
        'active' => array(
            'title'=>'Active',
            'type'=>Field::TYPE_BOOL,
            'subtype'=>'',
            'readonly'=>false,
            "default"=>array("value"=>"t", "on"=>"null")
        ),
        'last_login' => array(
            'title'=>'Last Login', 'type'=>'timestamp', 'subtype'=>'', 'readonly'=>true
        ),
        'image_id' => array(
            'title'=>'Image',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'file'
        ),
        'team_id' => array(
            'title'=>'Team',
            'type'=>Field::TYPE_GROUPING,
            'subtype'=>'user_teams',
            'fkey_table'=>array("key"=>"id", "title"=>"name", "parent"=>"parent_id")
        ),
        'groups' => array(
            'title'=>'Groups',
            'type'=>Field::TYPE_GROUPING_MULTI,
            'subtype'=>'object_groupings',
        ),
        'manager_id' => array(
            'title'=>'Manager',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'user'
        ),
    ),
);
