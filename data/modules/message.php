<?php

/**
 * Return navigation for entity of messages
 */

namespace modules\navigation;

use Netric\Account\Module\LeftNavItemTypes;

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
            "type" => LeftNavItemTypes::HEADER,
            "title" => "Rooms",
            "route" => "rooms",
        ],
        [
            "type" => LeftNavItemTypes::ENTITY_BROWSE_LEFTNAV,
            "route" => "rooms",
            "browser_view" => "my_rooms",
            "objType" => "chat_room"
        ],
        [
            "title" => "Browse All Rooms",
            "type" => LeftNavItemTypes::ENTITY_BROWSE,
            "route" => "rooms",
            "objType" => "chat_room",
            "icon" => "SearchIcon",
        ],
    ],
];
