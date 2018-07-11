<?php
namespace data\entity_definitions;
use Netric\EntityDefinition\Field;

return array(
    'is_private' => true,
    'default_activity_level' => 1,
    'store_revisions' => true,
    'fields' => array(
        // Textual name of the account
        'name' => array(
            'title'=>'Title',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>false,
            'require'=>true
        ),

        "type" => array(
            'title'=>'Server Type',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'4',
            'readonly'=>false,
            'optional_values'=>array(
                "none"=>"None - just reply from this address",
                "imap"=>"IMAP",
                "pop3"=>"POP3",
            ),
        ),

        'address' => array(
            'title'=>'Email Address',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>false,
            'require'=>true
        ),

        'reply_to' => array(
            'title'=>'Reply To',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>false
        ),

        'signature' => array(
            'title'=>'Signature',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>false
        ),

        'host' => array(
            'title'=>'Host',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>false,
            'require'=>true
        ),

        'username' => array(
            'title'=>'Username',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>false,
            'require'=>true
        ),

        'password' => array(
            'title'=>'Password',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>false,
            'require'=>true
        ),

        'port' => array(
            'title'=>'Port',
            'type'=>Field::TYPE_NUMBER,
            'subtype'=>'integer',
            'readonly'=>false,
            'require'=>true
        ),

        'f_default' => array(
            'title'=>'Default Account',
            'type'=>Field::TYPE_BOOL,
            'readonly'=>false
        ),

        'f_ssl' => array(
            'title'=>'Require SSL',
            'type'=>Field::TYPE_BOOL,
            'readonly'=>false
        ),

        'sync_data' => array(
            'title'=>'Sync Data',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>false
        ),

        'ts_last_full_sync' => array(
            'title'=>'Last Full Sync',
            'type'=>Field::TYPE_TIMESTAMP,
            'subtype'=>'',
            'readonly'=>false
        ),

        'f_synchronizing' => array(
            'title'=>'Sync In Process',
            'type'=>Field::TYPE_BOOL,
            'subtype'=>'true'
        ),

        'f_system' => array(
            'title'=>'System',
            'type'=>Field::TYPE_BOOL,
            'readonly'=>false
        ),

        'f_outgoing_auth' => array(
            'title'=>'Outgoing Auth',
            'type'=>Field::TYPE_BOOL,
            'readonly'=>false
        ),

        'host_out' => array(
            'title'=>'Host Out',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>false
        ),

        'port_out' => array(
            'title'=>'Port Out',
            'type'=>Field::TYPE_NUMBER,
            'subtype'=>'integer',
            'readonly'=>false
        ),

        'f_ssl_out' => array(
            'title'=>'SSL Out',
            'type'=>Field::TYPE_BOOL,
            'readonly'=>false
        ),

        'username_out' => array(
            'title'=>'Username Out',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>false
        ),

        'password_out' => array(
            'title'=>'Password Out',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>false
        ),

        'forward' => array(
            'title'=>'Forward',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>false
        ),
    ),
);
