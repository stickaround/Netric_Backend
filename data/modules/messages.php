<?php
/**
 * Return navigation for entity of messages
 */
namespace modules\navigation;

return array(
    "title" => "Messages",
    "icon" => "EmailIcon",
    "default_route" => "inbox",
    "name" => "messages",
    "short_title" => 'Messages',
    "scope" => 'system',
    "settings" => "Plugin_Messages_Settings",
    "sort_order" => '2',
    "f_system" => 't',
    "navigation" => array(
        array(
            "title" => "Inbox",
            "type" => "browse",
            "route" => "inbox",
            "objType" => "email_thread",
            "icon" => "EmailIcon",
        ),
        array(
            "title" => "Notifications",
            "type" => "browse",
            "route" => "all-notifications",
            "objType" => "notification",
            "icon" => "announcement"
        ),
        array(
            "title" => "All Messages",
            "type" => "browse",
            "route" => "all-messages",
            "objType" => "email_account",
            "icon" => "tags",
            //"browseby" => "groups",
        )
    )
);
