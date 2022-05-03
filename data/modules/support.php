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
    "default_route" => "inbox",
    "name" => "support",
    "short_title" => 'Support',
    "scope" => 'system',
    "sort_order" => 5,
    "f_system" => true,
    "navigation" => [
        [
            "type" => LeftNavItemTypes::ENTITY_MODAL,
            "title" => "New Ticket",
            "route" => "newticket",
            "icon" => "AddBoxIcon",
            "objType" => ObjectTypes::TICKET,
        ],
        [
            "title" => "Inbox",
            "type" => LeftNavItemTypes::ENTITY_BROWSE,
            "browser_view" => "unseen_tickets",
            "objType" => ObjectTypes::TICKET,
            "route" => "inbox",
            "icon" => "InboxIcon",
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
            "title" => "Unassigned Tickets",
            "type" => LeftNavItemTypes::ENTITY_BROWSE,
            "browser_view" => "unassigned_tickets",
            "route" => "unassigned",
            "objType" => ObjectTypes::TICKET,
            "icon" => "OutlineNoAccountsIcon",
        ],
        [
            "title" => "All Tickets",
            "type" => LeftNavItemTypes::ENTITY_BROWSE,
            "browser_view" => "all_tickets",
            "objType" => ObjectTypes::TICKET,
            "route" => "all-tickets",
            "icon" => "DoneAllIcon",
        ],
        [
            "title" => "Contacts",
            "type" => LeftNavItemTypes::ENTITY_BROWSE,
            "route" => "all-contacts",
            "objType" => ObjectTypes::CONTACT,
            "icon" => "ViewListIcon",
            "browser_view" => "allactive",
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
            "browser_view" => "all_channels",
            "objType" => ObjectTypes::TICKET_CHANNEL,
            "icon" => "SearchIcon",
        ]
    ]
];
