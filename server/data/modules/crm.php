<?php
/**
 * Return navigation for entity of object type 'crm'
 */
namespace modules\navigation;

return array(
    "title" => "CRM",
    "icon" => "child",
    "default_route" => "all-customers",
    "navigation" => array(
        array(
            "title" => "New Customer",
            "type" => "entity",
            "route" => "new-customer",
            "objType" => "customer",
            "icon" => "AddIcon",
        ),
        array(
            "title" => "All Customers",
            "type" => "browse",
            "route" => "all-customers",
            "objType" => "customer",
            "icon" => "ViewListIcon",
            "browseby" => "groups",
        ),
        array(
            "title" => "New Lead",
            "type" => "entity",
            "route" => "new-lead",
            "objType" => "lead",
            "icon" => "AddIcon",
        ),
        array(
            "title" => "All Leads",
            "type" => "browse",
            "route" => "all-leads",
            "objType" => "lead",
            "icon" => "ViewListIcon",
            "browseby" => "groups",
        ),
        array(
            "title" => "New Opportunity",
            "type" => "entity",
            "route" => "new-opportunity",
            "objType" => "opportunity",
            "icon" => "AddIcon",
        ),
        array(
            "title" => "All Opportunities",
            "type" => "browse",
            "route" => "all-opportunity",
            "objType" => "opportunity",
            "icon" => "ViewListIcon",
            "browseby" => "groups",
        ),
        array(
            "title" => "New Campaign",
            "type" => "entity",
            "route" => "new-campaign",
            "objType" => "marketing_campaign",
            "icon" => "AddIcon",
        ),
        array(
            "title" => "All Campaigns",
            "type" => "browse",
            "route" => "all-campaigns",
            "objType" => "marketing_campaign",
            "icon" => "ViewListIcon",
            "browseby" => "groups",
        ),
        array(
            "title" => "New Case",
            "type" => "entity",
            "route" => "new-case",
            "objType" => "case",
            "icon" => "AddIcon",
        ),
        array(
            "title" => "All Cases",
            "type" => "browse",
            "route" => "all-cases",
            "objType" => "case",
            "icon" => "ViewListIcon",
            "browseby" => "groups",
        )
    )
);