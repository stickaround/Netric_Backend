<?php

/**
 * Return navigation for entity of messages
 */

namespace modules\navigation;

return [
    "title" => "Message",
    "icon" => "EmailIcon",
    "default_route" => "email",
    "name" => "messages",
    "short_title" => 'Messages',
    "scope" => 'system',
    "settings" => "Plugin_Messages_Settings",
    "sort_order" => '2',
    "f_system" => 't',
    "navigation" => [
        [
            "type" => "list-subheader",
            "title" => "Rooms",
            "route" => "rooms",
        ],
        [
            "type" => "browse-leftnav",
            "route" => "rooms",
            "browser_view" => "my_rooms",
            "objType" => "chat_room"
        ],
        [
            "title" => "Browse All Rooms",
            "type" => "browse",
            "route" => "browse_rooms",
            "objType" => "chat_room",
            "icon" => "SearchIcon",
        ],
        [
            "type" => "list-subheader",
            "title" => "Direct Messages",
            "route" => "direct",
        ],
        [
            "type" => "browse-leftnav",
            "route" => "rooms",
            "browser_view" => "my_direct_messages",
            "objType" => "chat_room"
        ],
    ],
];
