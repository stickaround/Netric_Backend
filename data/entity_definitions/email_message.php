<?php
namespace data\entity_definitions;
use Netric\EntityDefinition\Field;

return array(
    'is_private' => true,
    'default_activity_level' => 1,
    'parent_field' => 'mailbox_id',
    'store_revisions' => false,
    'fields' => array(
        'subject' => array(
            'title'=>'Subject',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>true
        ),
        'message_id' => array(
            'title'=>'Message Id',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'128',
            'readonly'=>true
        ),
        'send_to' => array(
            'title'=>'To',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>true
        ),
        'sent_from' => array(
            'title'=>'From',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>true
        ),
        'cc' => array(
            'title'=>'CC',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>true
        ),
        'bcc' => array(
            'title'=>'BCC',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>true
        ),
        'reply_to' => array(
            'title'=>'Reply To',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'128',
            'readonly'=>true
        ),
        'priority' => array(
            'title'=>'Priority',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'16',
            'readonly'=>true
        ),
        'file_id' => array(
            'title'=>'File Id',
            'type'=>Field::TYPE_INTEGER,
            'subtype'=>'',
            'readonly'=>true
        ),
        'flag_seen' => array(
            'title'=>'Seen',
            'type'=>Field::TYPE_BOOL,
            'subtype'=>'',
            'readonly'=>false
        ),
        'flag_draft' => array(
            'title'=>'Draft',
            'type'=>Field::TYPE_BOOL,
            'subtype'=>'',
            'readonly'=>true
        ),
        'flag_answered' => array(
            'title'=>'Answered',
            'type'=>Field::TYPE_BOOL,
            'subtype'=>'',
            'readonly'=>true
        ),
        'flag_flagged' => array(
            'title'=>'Flagged',
            'type'=>Field::TYPE_BOOL,
            'subtype'=>'',
            'readonly'=>false
        ),
        'flag_spam' => array(
            'title'=>'Is Spam',
            'type'=>Field::TYPE_BOOL,
            'subtype'=>'',
            'readonly'=>true
        ),
        'spam_report' => array(
            'title'=>'Spam Report',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>true
        ),
        'content_type' => array(
            'title'=>'Content Type',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'256',
            'readonly'=>true
        ),
        'return_path' => array(
            'title'=>'Return Path',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'128',
            'readonly'=>true
        ),
        'in_reply_to' => array(
            'title'=>'Return Path',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'128',
            'readonly'=>true
        ),
        'message_size' => array(
            'title'=>'Message Size',
            'type'=>Field::TYPE_INTEGER,
            'subtype'=>'',
            'readonly'=>true
        ),
        'num_attachments' => array(
            'title'=>'Num Attachments',
            'type'=>Field::TYPE_INTEGER,
            'subtype'=>'',
            'readonly'=>true
        ),
        'thread_count' => array(
            'title'=>'Thread Count',
            'type'=>Field::TYPE_INTEGER,
            'subtype'=>'',
            'readonly'=>true
        ),
        'orig_header' => array(
            'title'=>'Full Header',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>true
        ),
        'keywords' => array(
            'title'=>'Keywords',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>true
        ),
        'f_indexed' => array(
            'title'=>'Indexed',
            'type'=>Field::TYPE_BOOL,
            'subtype'=>'',
            'readonly'=>true
        ),
        'body' => array(
            'title'=>'Body',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'',
            'readonly'=>true
        ),
        'body_type' => array(
            'title'=>'Body Content Type',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'32',
            'readonly'=>true
        ),
        'parse_rev' => array(
            'title'=>'Indexed',
            'type'=>Field::TYPE_NUMBER,
            'subtype'=>'integer',
            'readonly'=>true
        ),
        'message_date' => array(
            'title'=>'Message Date',
            'type'=>Field::TYPE_TIMESTAMP,
            'subtype'=>'',
            'readonly'=>true,
            'default'=>array("value"=>"now", "on"=>"create")
        ),
        'mailbox_id' => array(
            'title'=>'Mailbox',
            'type'=>Field::TYPE_GROUPING,
            'subtype'=>'email_mailboxes',
            'fkey_table'=>array(
                "key"=>"id",
                "title"=>"name"
            ),
            'readonly'=>false
        ),
        'thread' => array(
            'title'=>'Thread',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'email_thread',
            'fkey_table'=>array("key"=>"id", "title"=>"subject"),
            'readonly'=>true,
        ),
        'email_account' => array(
            'title'=>'Email Account',
            'type'=>Field::TYPE_OBJECT,
            'subtype'=>'email_account',
            'readonly'=>true
        ),
        'message_uid' => array(
            'title'=>'Message Uid',
            'type'=>Field::TYPE_TEXT,
            'subtype'=>'128',
            'readonly'=>false
        ),
    ),
);
