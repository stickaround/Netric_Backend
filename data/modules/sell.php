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
            "title" => "Contacts",
            "type" => LeftNavItemTypes::ENTITY_BROWSE,
            "route" => "contacts",
            "objType" => ObjectTypes::CONTACT,
            "icon" => "ViewListIcon"
        ],
        [
            "title" => "Leads",
            "type" => LeftNavItemTypes::ENTITY_BROWSE,
            "route" => "leads",
            "objType" => ObjectTypes::CONTACT,
            "icon" => "ViewListIcon"
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
