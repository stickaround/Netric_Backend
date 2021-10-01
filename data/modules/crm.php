<?php

/**
 * Return navigation for entity of object type 'crm'
 */

namespace modules\navigation;

use Netric\Account\Module\LeftNavItemTypes;
use Netric\EntityDefinition\ObjectTypes;

return [
    "title" => "CRM",
    "icon" => "ContactsIcon",
    "default_route" => "all-customers",
    "name" => "crm",
    "short_title" => 'CRM',
    "scope" => 'system',
    "sort_order" => '4',
    "f_system" => true,
    "navigation" => [
        [
            "title" => "All Customers",
            "type" => LeftNavItemTypes::ENTITY_BROWSE,
            "route" => "all-customers",
            "objType" => "customer",
            "icon" => "ViewListIcon",
            "browseby" => "groups",
        ],
        [
            "title" => "All Leads",
            "type" => LeftNavItemTypes::ENTITY_BROWSE,
            "route" => "all-leads",
            "objType" => "lead",
            "icon" => "ViewListIcon",
            "browseby" => "groups",
        ],
        [
            "title" => "All Opportunities",
            "type" => LeftNavItemTypes::ENTITY_BROWSE,
            "route" => "all-opportunity",
            "objType" => "opportunity",
            "icon" => "ViewListIcon",
            "browseby" => "groups",
        ],
        [
            "title" => "All Campaigns",
            "type" => LeftNavItemTypes::ENTITY_BROWSE,
            "route" => "all-campaigns",
            "objType" => "marketing_campaign",
            "icon" => "ViewListIcon",
            "browseby" => "groups",
        ],
        [
            "title" => "All Tickets",
            "type" => LeftNavItemTypes::ENTITY_BROWSE,
            "route" => "all-cases",
            "objType" => ObjectTypes::TICKET,
            "icon" => "ViewListIcon",
            "browseby" => "groups",
        ]
    ]
];
