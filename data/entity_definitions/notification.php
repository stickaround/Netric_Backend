<?php

namespace data\entity_definitions;

use Netric\EntityDefinition\Field;

return array(
    'is_private' => true,
    'default_activity_level' => 1,
    'store_revisions' => false,
    'fields' => array(
        // Textual name or subject
        'name' => array(
            'title' => 'Title',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => false,
            'require' => true,
        ),

        // Notification content text
        'description' => array(
            'title' => 'Description',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => false,
        ),

        // The object we are reminding on
        'obj_reference' => array(
            'title' => 'Concering',
            'type' => Field::TYPE_OBJECT,
            'subtype' => '',
            'readonly' => false,
        ),

        // Flag indicating if the notification has been seen
        'f_seen' => array(
            'title' => 'Seen',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => false,
            'default' => array(
                "on" => "null",
                "value" => "f",
            ),
        ),

        // Flag indicating if the notification has been shown already
        'f_shown' => array(
            'title' => 'Showed',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => false,
            'default' => array(
                "on" => "null",
                "value" => false,
            ),
        ),

        // Flag indicating if the notification should be a popup
        'f_popup' => array(
            'title' => 'Popup Alert',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => false,
        ),

        // Flag indicating if the notification should be emailed
        'f_email' => array(
            'title' => 'Send Email',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => false,
        ),

        // Flag indicating if the notification should be pushed
        'f_push' => array(
            'title' => 'Send Push',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => false,
        ),

        // Flag indicating if the notification should be text messaged
        'f_sms' => array(
            'title' => 'Send SMS',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => false,
        ),

        // The actual time when this reminder should execute
        'ts_execute' => array(
            'title' => 'Execute Time',
            'type' => Field::TYPE_TIMESTAMP,
            'subtype' => '',
            'readonly' => false,
            'default' => array(
                "on" => "null",
                "value" => "now",
            ),
        ),
    ),
);
