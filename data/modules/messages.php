<?php
/**
 * Return navigation for entity of messages
 */
namespace modules\navigation;

return array(
    "title" => "Messages",
    "icon" => "EmailIcon",
    "default_route" => "email",
    "name" => "messages",
    "short_title" => 'Messages',
    "scope" => 'system',
    "settings" => "Plugin_Messages_Settings",
    "sort_order" => '2',
    "f_system" => 't',
    "navigation" => array(
        array(
            "title" => "Chat",
            "type" => "browse",
            "route" => "chat",
            "objType" => "chat_thread",
            "icon" => "CommentIcon",
        ),
        array(
            "title" => "Email",
            "type" => "browse",
            "route" => "email",
            "objType" => "email_thread",
            "icon" => "EmailIcon",
        ),
        array(
            "title" => "Notifications",
            "type" => "browse",
            "route" => "notifications",
            "objType" => "notification",
            "icon" => "LightBulbIcon"
        )
    )
);
