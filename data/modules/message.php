<?php

/**
 * Return navigation for entity of messages
 */

namespace modules\navigation;

return [
    "title" => "Messages",
    "icon" => "MessageIcon",
    "default_route" => "rooms",
    "name" => "message",
    "short_title" => 'Message',
    "scope" => 'system',
    "sort_order" => '2',
    "f_system" => true,
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
            "route" => "rooms",
            "objType" => "chat_room",
            "icon" => "SearchIcon",
        ],
    ],
];
