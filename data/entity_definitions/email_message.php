<?php

namespace data\entity_definitions;

use Netric\EntityDefinition\Field;

return [
    'is_private' => true,
    'default_activity_level' => 1,
    'parent_field' => '',
    'store_revisions' => false,
    'fields' => [
        'subject' => [
            'title' => 'Subject',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => true
        ],
        'message_id' => [
            'title' => 'Message Id',
            'type' => Field::TYPE_TEXT,
            'subtype' => '128',
            'readonly' => true
        ],
        'to' => [
            'title' => 'To',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => true
        ],
        'from' => [
            'title' => 'From',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => true
        ],
        'cc' => [
            'title' => 'CC',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => true
        ],
        'bcc' => [
            'title' => 'BCC',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => true
        ],
        'reply_to' => [
            'title' => 'Reply To',
            'type' => Field::TYPE_TEXT,
            'subtype' => '128',
            'readonly' => true
        ],
        'priority' => [
            'title' => 'Priority',
            'type' => Field::TYPE_TEXT,
            'subtype' => '16',
            'readonly' => true
        ],
        'file_id' => [
            'title' => 'File Id',
            'type' => Field::TYPE_INTEGER,
            'subtype' => '',
            'readonly' => true
        ],
        'flag_seen' => [
            'title' => 'Seen',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => false
        ],
        'flag_draft' => [
            'title' => 'Draft',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => true
        ],
        'flag_answered' => [
            'title' => 'Answered',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => true
        ],
        'flag_flagged' => [
            'title' => 'Flagged',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => false
        ],
        'flag_spam' => [
            'title' => 'Is Spam',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => true
        ],
        'spam_report' => [
            'title' => 'Spam Report',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => true
        ],
        'content_type' => [
            'title' => 'Content Type',
            'type' => Field::TYPE_TEXT,
            'subtype' => '256',
            'readonly' => true
        ],
        'return_path' => [
            'title' => 'Return Path',
            'type' => Field::TYPE_TEXT,
            'subtype' => '128',
            'readonly' => true
        ],
        'in_reply_to' => [
            'title' => 'Return Path',
            'type' => Field::TYPE_TEXT,
            'subtype' => '128',
            'readonly' => true
        ],
        'message_size' => [
            'title' => 'Message Size',
            'type' => Field::TYPE_INTEGER,
            'subtype' => '',
            'readonly' => true
        ],
        'num_attachments' => [
            'title' => 'Num Attachments',
            'type' => Field::TYPE_INTEGER,
            'subtype' => '',
            'readonly' => true
        ],
        'thread_count' => [
            'title' => 'Thread Count',
            'type' => Field::TYPE_INTEGER,
            'subtype' => '',
            'readonly' => true
        ],
        'orig_header' => [
            'title' => 'Full Header',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => true
        ],
        'keywords' => [
            'title' => 'Keywords',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => true
        ],
        'f_indexed' => [
            'title' => 'Indexed',
            'type' => Field::TYPE_BOOL,
            'subtype' => '',
            'readonly' => true
        ],
        'body' => [
            'title' => 'Body',
            'type' => Field::TYPE_TEXT,
            'subtype' => '',
            'readonly' => true
        ],
        'body_type' => [
            'title' => 'Body Content Type',
            'type' => Field::TYPE_TEXT,
            'subtype' => '32',
            'readonly' => true
        ],
        'parse_rev' => [
            'title' => 'Indexed',
            'type' => Field::TYPE_NUMBER,
            'subtype' => 'integer',
            'readonly' => true
        ],
        'message_date' => [
            'title' => 'Message Date',
            'type' => Field::TYPE_TIMESTAMP,
            'subtype' => '',
            'readonly' => true,
            'default' => ["value" => "now", "on" => "create"],
        ],
        'mailbox_id' => [
            'title' => 'Mailbox',
            'type' => Field::TYPE_GROUPING,
            // 'subtype' => 'email_mailboxes',
            // 'fkey_table' => [
            //     "key" => "id",
            //     "title" => "name"
            // ],
            'readonly' => false
        ],
        'thread' => [
            'title' => 'Thread',
            'type' => Field::TYPE_OBJECT,
            'subtype' => 'email_thread',
            'fkey_table' => ["key" => "id", "title" => "subject"],
            'readonly' => true,
        ],
        'email_account' => [
            'title' => 'Email Account',
            'type' => Field::TYPE_OBJECT,
            'subtype' => 'email_account',
            'readonly' => true
        ],
        'message_uid' => [
            'title' => 'Message Uid',
            'type' => Field::TYPE_TEXT,
            'subtype' => '128',
            'readonly' => false
        ],
    ],
];
