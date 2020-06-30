<?php

/**
 * Return navigation for entity of object type 'crm'
 */

namespace modules\navigation;

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
            "type" => "browse",
            "route" => "all-customers",
            "objType" => "customer",
            "icon" => "ViewListIcon",
            "browseby" => "groups",
        ],
        [
            "title" => "All Leads",
            "type" => "browse",
            "route" => "all-leads",
            "objType" => "lead",
            "icon" => "ViewListIcon",
            "browseby" => "groups",
        ],
        [
            "title" => "All Opportunities",
            "type" => "browse",
            "route" => "all-opportunity",
            "objType" => "opportunity",
            "icon" => "ViewListIcon",
            "browseby" => "groups",
        ],
        [
            "title" => "All Campaigns",
            "type" => "browse",
            "route" => "all-campaigns",
            "objType" => "marketing_campaign",
            "icon" => "ViewListIcon",
            "browseby" => "groups",
        ],
        [
            "title" => "All Cases",
            "type" => "browse",
            "route" => "all-cases",
            "objType" => "case",
            "icon" => "ViewListIcon",
            "browseby" => "groups",
        ]
    ]
];
