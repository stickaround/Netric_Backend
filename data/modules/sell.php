<?php

/**
 * Return navigation for entity of object type 'crm'
 */

namespace modules\navigation;

use Netric\Account\Module\LeftNavItemTypes;
use Netric\EntityDefinition\ObjectTypes;

return [
    "title" => "Sell",
    "icon" => "ContactsIcon",
    "default_route" => "contacts",
    "name" => "sell",
    "short_title" => 'Sell',
    "scope" => 'system',
    "sort_order" => 6,
    "f_system" => true,
    "navigation" => [
        [
            "title" => "All Contacts",
            "type" => LeftNavItemTypes::ENTITY_BROWSE,
            "route" => "contacts",
            "objType" => ObjectTypes::CONTACT,
            "icon" => "ViewListIcon",
            "browser_view" => "allactive",
        ],
        [
            "title" => "My Contacts",
            "type" => LeftNavItemTypes::ENTITY_BROWSE,
            "route" => "my-contacts",
            "objType" => ObjectTypes::CONTACT,
            "icon" => "ViewListIcon",
            "browser_view" => "my",
        ],
        [
            "title" => "Leads",
            "type" => LeftNavItemTypes::ENTITY_BROWSE,
            "route" => "leads",
            "objType" => ObjectTypes::CONTACT,
            "icon" => "ViewListIcon",
            "browser_view" => "leads",
        ],
        [
            "title" => "Prospects",
            "type" => LeftNavItemTypes::ENTITY_BROWSE,
            "route" => "prospects",
            "objType" => ObjectTypes::CONTACT,
            "icon" => "ViewListIcon",
            "browser_view" => "prospects",
        ],
        [
            "title" => "Customers",
            "type" => LeftNavItemTypes::ENTITY_BROWSE,
            "route" => "customers",
            "objType" => ObjectTypes::CONTACT,
            "icon" => "ViewListIcon",
            "browser_view" => "customers",
        ],
        [
            "title" => "Inactive/Past Customers",
            "type" => LeftNavItemTypes::ENTITY_BROWSE,
            "route" => "inactive",
            "objType" => ObjectTypes::CONTACT,
            "icon" => "ViewListIcon",
            "browser_view" => "inactive",
        ],
        [
            "type" => LeftNavItemTypes::HEADER,
            "title" => "Opportunities",
            "route" => "opportunities"
        ],
        // [
        //     "type" => LeftNavItemTypes::ENTITY_BROWSE_LEFTNAV,
        //     "route" => "queues",
        //     "browser_view" => "my_channels",
        //     "objType" => ObjectTypes::TICKET_CHANNEL,
        //     "icon" => "DoneAllIcon",
        // ],
        [
            "title" => "Opportunities",
            "type" => LeftNavItemTypes::ENTITY_BROWSE,
            "route" => "all-opportunity",
            "objType" => ObjectTypes::OPPORTUNITY,
            "icon" => "ViewListIcon",
        ],
    ]
];
