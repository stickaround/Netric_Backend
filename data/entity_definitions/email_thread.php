<?php

namespace data\entity_definitions;

use Netric\EntityDefinition\Field;

return array(
    'is_private' => true,
    'default_activity_level' => 1,
    'fields' => array(
        'subject' => array(
            'title' => 'Subject',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => true,
        ),
        'body' => array(
            'title' => 'Body',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => true,
        ),
        'keywords' => array(
            'title' => 'Keywords',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => true
        ),
        'senders' => array(
            'title' => 'From',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => true
        ),
        'receivers' => array(
            'title' => 'To',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => true
        ),
        'num_attachments' => array(
            'title' => 'Num Attachments',
            'type' => Field::TYPE_INTEGER,
            'subtype' => '',
            'readonly' => true,
            "default" => array("value" => "0", "on" => "null")
        ),
        'num_messages' => array(
            'title' => 'Num Messages',
            'type' => Field::TYPE_INTEGER,
            'subtype' => '',
            'readonly' => true
        ),
        'f_seen' => array(
            'title' => 'Seen',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => false
        ),
        'f_flagged' => array(
            'title' => 'Flagged',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => false
        ),
        'time_updated' => array(
            'title' => 'Time Changed',
            'type' => Field::TYPE_TIMESTAMP,
            'subtype' => '',
            'readonly' => true,
            'default' => array("value" => "now", "on" => "update")
        ),
        'ts_delivered' => array(
            'title' => 'Time Delivered',
            'type' => Field::TYPE_TIMESTAMP,
            'subtype' => '',
            'readonly' => true,
            'default' => array("value" => "now", "on" => "create")
        ),
        'mailbox_id' => array(
            'title' => 'Groups',
            'type' => Field::TYPE_GROUPING_MULTI,
            // 'subtype' => 'email_mailboxes',
            // 'fkey_table' => array(
            //     "key" => "id",
            //     "title" => "name",
            //     "parent" => "parent_box",
            //     "filter" => array(
            //         "user_id" => "owner_id"
            //     ),
            //     "ref_table" => array(
            //         "table" => "email_thread_mailbox_mem",
            //         "this" => "thread_id",
            //         "ref" => "mailbox_id"
            //     )
            // )
        ),
    ),
);
