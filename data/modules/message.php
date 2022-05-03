<?php

/**
 * Return navigation for entity of messages
 */

namespace modules\navigation;

use Netric\Account\Module\LeftNavItemTypes;

return [
    "title" => "Messages",
    "icon" => "ChatBubbleIcon",
    "default_route" => "direct",
    "name" => "message",
    "short_title" => 'Chat',
    "scope" => 'system',
    "sort_order" => 2,
    "f_system" => true,
    "navigation" => [
        [
            // TODO: Change to ENTITY_MODAL
            // Created a separate task for this. Since I also need to modify the members container and re-implement the scope data.
            // Followup Task: https://app.netric.com/#/work/ent/7ad59d1c-037c-413b-9655-ae135403d231
            "type" => LeftNavItemTypes::ENTITY,
            "title" => "New Message",
            "route" => "newdirect",
            "icon" => "ChatIcon",
            "objType" => "chat_room",
            "data" => [
                "scope" => "direct"
            ]
        ],
        [
            // TODO: Change to ENTITY_MODAL
            "type" => LeftNavItemTypes::ENTITY,
            "title" => "New Room",
            "route" => "newroom",
            "icon" => "ForumIcon",
            "objType" => "chat_room",
            "data" => [
                "scope" => "channel"
            ]
        ],
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
            "browser_view" => "my_rooms",
            "icon" => "SearchIcon",
        ],
        [
            "type" => LeftNavItemTypes::HEADER,
            "title" => "Direct",
            "route" => "direct",
        ],
        [
            "type" => LeftNavItemTypes::ENTITY_BROWSE_LEFTNAV,
            "route" => "direct",
            "browser_view" => "my_direct_messages",
            "objType" => "chat_room"
        ],
        [
            "title" => "All Direct Messages",
            "type" => LeftNavItemTypes::ENTITY_BROWSE,
            "route" => "direct",
            "objType" => "chat_room",
            "icon" => "SearchIcon",
            "browser_view" => "my_direct_messages",
        ],
    ],
];
