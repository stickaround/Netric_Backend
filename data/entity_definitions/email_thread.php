<?php
namespace data\entity_definitions;

return array(
    'is_private' => true,
    'default_activity_level' => 1,
    'fields' => array(
        'subject' => array(
            'title'=>'Subject',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>true,
        ),
        'body' => array(
            'title'=>'Body',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>true,
        ),
        'keywords' => array(
            'title'=>'Keywords',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>true
        ),
        'senders' => array(
            'title'=>'From',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>true
        ),
        'receivers' => array(
            'title'=>'To',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>true
        ),
        'num_attachments' => array(
            'title'=>'Num Attachments',
            'type'=>'integer',
            'subtype'=>'',
            'readonly'=>true,
            "default"=>array("value"=>"0", "on"=>"null")
        ),
        'num_messages' => array(
            'title'=>'Num Messages',
            'type'=>'integer',
            'subtype'=>'',
            'readonly'=>true
        ),
        'f_seen' => array(
            'title'=>'Seen',
            'type'=>'bool',
            'subtype'=>'',
            'readonly'=>false
        ),
        'f_flagged' => array(
            'title'=>'Flagged',
            'type'=>'bool',
            'subtype'=>'',
            'readonly'=>false
        ),
        'time_updated' => array(
            'title'=>'Time Changed',
            'type'=>'timestamp',
            'subtype'=>'',
            'readonly'=>true,
            'default'=>array("value"=>"now", "on"=>"update")
        ),
        'ts_delivered' => array(
            'title'=>'Time Delivered',
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
            'title'=>'Groups',
            'type'=>'fkey_multi',
            'subtype'=>'email_mailboxes',
            'fkey_table'=>array(
                "key"=>"id",
                "title"=>"name",
                "parent"=>"parent_box",
                "filter"=>array(
                    "user_id"=>"owner_id"
                ),
                "ref_table"=>array(
                    "table"=>"email_thread_mailbox_mem",
                    "this"=>"thread_id",
                    "ref"=>"mailbox_id"
                )
            )
        ),
    ),
);
