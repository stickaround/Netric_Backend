<?php

namespace modules\navigation;

use Netric\EntityDefinition\ObjectTypes;
use Netric\Account\Module\LeftNavItemTypes;

/**
 * TODO: this is a work in progress and not yet published
 */

return [
    "title" => "Support",
    "icon" => "WorkIcon",
    "default_route" => "my-tickets",
    "name" => "support",
    "short_title" => 'Support',
    "scope" => 'system',
    "sort_order" => 5,
    "f_system" => true,
    "navigation" => [
        [
            "type" => LeftNavItemTypes::ENTITY,
            "title" => "New Ticket",
            "route" => "newticket",
            "icon" => "AddBoxIcon",
            "objType" => ObjectTypes::TICKET,
        ],
        [
            "title" => "My Tickets",
            "type" => LeftNavItemTypes::ENTITY_BROWSE,
            "browser_view" => "my_tickets",
            "objType" => ObjectTypes::TICKET,
            "route" => "my-tickets",
            "icon" => "CheckIcon",
        ],
        [
            "type" => LeftNavItemTypes::HEADER,
            "title" => "Channels",
            "route" => "channels"
        ],
        [
            "type" => LeftNavItemTypes::ENTITY_BROWSE_LEFTNAV,
            "route" => "queues",
            "browser_view" => "my_channels",
            "objType" => ObjectTypes::TICKET_CHANNEL,
            "icon" => "DoneAllIcon",
        ],
        [
            "title" => "All Channels",
            "type" => LeftNavItemTypes::ENTITY_BROWSE,
            "route" => "channels",
            "objType" => ObjectTypes::TICKET_CHANNEL,
            "icon" => "SearchIcon",
        ]
    ]
];
