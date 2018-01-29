<?php
namespace data\entity_definitions;

return array(
    'fields' => array(
        // User name
        'name' => array(
            'title'=>'User Name',
            'type'=>'text',
            'subtype'=>'128',
            'readonly'=>false,
            'required'=>true,
            'unique'=>true,
        ),

        // Full name first + last
        'full_name' => array(
            'title'=>'Full Name',
            'type'=>'text',
            'subtype'=>'128',
            'readonly'=>false,
            'required'=>true
        ),

        'password' => array(
            'title'=>'Password',
            'type'=>'text',
            'subtype'=>'password',
            'readonly'=>false
        ),

        'password_salt' => array(
            'title'=>'PW Salt',
            'type'=>'text',
            'subtype'=>'password',
            'readonly'=>true
        ),

        'theme' => array(
            'title'=>'Theme',
            'type'=>'text',
            'subtype'=>'32',
        ),

        'timezone' => array(
            'title'=>'Timezone',
            'type'=>'text',
            'subtype'=>'64',
        ),

        'notes' => array(
            'title'=>'About',
            'type'=>'text',
            'subtype'=>'',
        ),

        'email' => array(
            'title'=>'Email',
            'type'=>'text',
            'subtype'=>'256',
            'readonly'=>false,
            'required'=>false
        ),

        'phone_office' => array(
            'title'=>'Office Phone',
            'type'=>'text',
            'subtype'=>'64',
            'readonly'=>false,
            'mask'=>'phone_dash'
        ),

        'phone_ext' => array(
            'title'=>'Phone Ext.',
            'type'=>'text',
            'subtype'=>'16',
            'readonly'=>false
        ),

        'phone_mobile' => array(
            'title'=>'Mobile Phone',
            'type'=>'text',
            'subtype'=>'64',
            'readonly'=>false,
            'mask'=>'phone_dash'
        ),

        'phone_mobile_carrier' => array(
            'title'=>'Mobile Carrier',
            'type'=>'text',
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
            'type'=>'text',
            'subtype'=>'64',
            'readonly'=>false,
            'mask'=>'phone_dash'
        ),

        // Aereus customer number
        'customer_number' => array(
            'title'=>'Netric Customer Number',
            'type'=>'text',
            'subtype'=>'64',
            'readonly'=>true,
        ),

        'job_title' => array(
            'title'=>'Job Title', 'type'=>'text', 'subtype'=>'256', 'readonly'=>false, 'required'=>false
        ),
        'city' => array(
            'title'=>'City', 'type'=>'text', 'subtype'=>'256', 'readonly'=>false, 'required'=>false
        ),
        'state' => array(
            'title'=>'State', 'type'=>'text', 'subtype'=>'256', 'readonly'=>false, 'required'=>false
        ),
        'active' => array(
            'title'=>'Active', 'type'=>'bool', 'subtype'=>'', 'readonly'=>false, "default"=>array("value"=>"t", "on"=>"null")
        ),
        'last_login' => array(
            'title'=>'Last Login', 'type'=>'timestamp', 'subtype'=>'', 'readonly'=>true
        ),
        'image_id' => array(
            'title'=>'Image',
            'type'=>'object',
            'subtype'=>'file'
        ),
        'team_id' => array(
            'title'=>'Team',
            'type'=>'fkey',
            'subtype'=>'user_teams',
            'fkey_table'=>array("key"=>"id", "title"=>"name", "parent"=>"parent_id")
        ),
        'groups' => array(
            'title'=>'Groups',
            'type'=>'fkey_multi',
            'subtype'=>'object_groupings',
        ),
        'manager_id' => array(
            'title'=>'Manager',
            'type'=>'object',
            'subtype'=>'user'
        ),
    ),
);
