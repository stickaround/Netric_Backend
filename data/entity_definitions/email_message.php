<?php
namespace data\entity_definitions;

return array(
    'is_private' => true,
    'default_activity_level' => 1,
    'parent_field' => 'mailbox_id',
    'store_revisions' => false,
    'fields' => array(
        'subject' => array(
            'title'=>'Subject',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>true
        ),
        'message_id' => array(
            'title'=>'Message Id',
            'type'=>'text',
            'subtype'=>'128',
            'readonly'=>true
        ),
        'send_to' => array(
            'title'=>'To',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>true
        ),
        'sent_from' => array(
            'title'=>'From',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>true
        ),
        'cc' => array(
            'title'=>'CC',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>true
        ),
        'bcc' => array(
            'title'=>'BCC',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>true
        ),
        'reply_to' => array(
            'title'=>'Reply To',
            'type'=>'text',
            'subtype'=>'128',
            'readonly'=>true
        ),
        'priority' => array(
            'title'=>'Priority',
            'type'=>'text',
            'subtype'=>'16',
            'readonly'=>true
        ),
        'file_id' => array(
            'title'=>'File Id',
            'type'=>'integer',
            'subtype'=>'',
            'readonly'=>true
        ),
        'flag_seen' => array(
            'title'=>'Seen',
            'type'=>'bool',
            'subtype'=>'',
            'readonly'=>false
        ),
        'flag_draft' => array(
            'title'=>'Draft',
            'type'=>'bool',
            'subtype'=>'',
            'readonly'=>true
        ),
        'flag_answered' => array(
            'title'=>'Answered',
            'type'=>'bool',
            'subtype'=>'',
            'readonly'=>true
        ),
        'flag_flagged' => array(
            'title'=>'Flagged',
            'type'=>'bool',
            'subtype'=>'',
            'readonly'=>false
        ),
        'flag_spam' => array(
            'title'=>'Is Spam',
            'type'=>'bool',
            'subtype'=>'',
            'readonly'=>true
        ),
        'spam_report' => array(
            'title'=>'Spam Report',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>true
        ),
        'content_type' => array(
            'title'=>'Content Type',
            'type'=>'text',
            'subtype'=>'256',
            'readonly'=>true
        ),
        'return_path' => array(
            'title'=>'Return Path',
            'type'=>'text',
            'subtype'=>'128',
            'readonly'=>true
        ),
        'in_reply_to' => array(
            'title'=>'Return Path',
            'type'=>'text',
            'subtype'=>'128',
            'readonly'=>true
        ),
        'message_size' => array(
            'title'=>'Message Size',
            'type'=>'integer',
            'subtype'=>'',
            'readonly'=>true
        ),
        'num_attachments' => array(
            'title'=>'Num Attachments',
            'type'=>'integer',
            'subtype'=>'',
            'readonly'=>true
        ),
        'thread_count' => array(
            'title'=>'Thread Count',
            'type'=>'integer',
            'subtype'=>'',
            'readonly'=>true
        ),
        'orig_header' => array(
            'title'=>'Full Header',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>true
        ),
        'keywords' => array(
            'title'=>'Keywords',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>true
        ),
        'f_indexed' => array(
            'title'=>'Indexed',
            'type'=>'bool',
            'subtype'=>'',
            'readonly'=>true
        ),
        'body' => array(
            'title'=>'Body',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>true
        ),
        'body_type' => array(
            'title'=>'Body Content Type',
            'type'=>'text',
            'subtype'=>'32',
            'readonly'=>true
        ),
        'parse_rev' => array(
            'title'=>'Indexed',
            'type'=>'number',
            'subtype'=>'integer',
            'readonly'=>true
        ),
        'message_date' => array(
            'title'=>'Message Date',
            'type'=>'timestamp',
            'subtype'=>'',
            'readonly'=>true,
            'default'=>array("value"=>"now", "on"=>"create")
        ),
        'owner_id' => array(
            'title'=>'User',
            'type'=>'object',
            'subtype'=>'user',
            'default'=>array("value"=>"-3", "on"=>"null")
        ),
        'mailbox_id' => array(
            'title'=>'Mailbox',
            'type'=>'fkey',
            'subtype'=>'email_mailboxes',
            'fkey_table'=>
                array(
                    "key"=>"id",
                    "title"=>
                        "name",
                    "filter"=>array(
                        "user_id"=>"owner_id"
                    ),
                    "parent"=>"parent_box"
                ),
            'readonly'=>false
        ),
        'thread' => array(
            'title'=>'Thread',
            'type'=>'object',
            'subtype'=>'email_thread',
            'fkey_table'=>array("key"=>"id", "title"=>"subject"),
            'readonly'=>true,
        ),
        'email_account' => array(
            'title'=>'Email Account',
            'type'=>'object',
            'subtype'=>'email_account',
            'readonly'=>true
        ),
        'message_uid' => array(
            'title'=>'Message Uid',
            'type'=>'text',
            'subtype'=>'128',
            'readonly'=>false
        ),
    ),
);
