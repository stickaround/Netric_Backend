<?php
/**
 * Return navigation for entity of object type 'crm'
 */
namespace objects\navigation;

return array(
    "xml_navigation" => array(
        "title" => "CRM",
        "icon" => "child",
        "defaultRoute" => "all-customers",
        "navigation" => array(
            array(
                "title" => "New Customer",
                "type" => "entity",
                "route" => "new-customer",
                "objType" => "customer",
                "icon" => "plus",
            ),
            array(
                "title" => "All Customers",
                "type" => "browse",
                "route" => "all-customers",
                "objType" => "customer",
                "icon" => "list-ul",
                "browseby" => "groups",
            ),
            array(
                "title" => "New Lead",
                "type" => "entity",
                "route" => "new-lead",
                "objType" => "lead",
                "icon" => "plus",
            ),
            array(
                "title" => "All Leads",
                "type" => "browse",
                "route" => "all-leads",
                "objType" => "lead",
                "icon" => "list-ul",
                "browseby" => "groups",
            ),
            array(
                "title" => "New Opportunity",
                "type" => "entity",
                "route" => "new-opportunity",
                "objType" => "opportunity",
                "icon" => "plus",
            ),
            array(
                "title" => "All Opportunities",
                "type" => "browse",
                "route" => "all-opportunity",
                "objType" => "opportunity",
                "icon" => "list-ul",
                "browseby" => "groups",
            ),
            array(
                "title" => "New Campaign",
                "type" => "entity",
                "route" => "new-campaign",
                "objType" => "marketing_campaign",
                "icon" => "plus",
            ),
            array(
                "title" => "All Campaigns",
                "type" => "browse",
                "route" => "all-campaigns",
                "objType" => "marketing_campaign",
                "icon" => "list-ul",
                "browseby" => "groups",
            ),
            array(
                "title" => "New Case",
                "type" => "entity",
                "route" => "new-case",
                "objType" => "case",
                "icon" => "plus",
            ),
            array(
                "title" => "All Cases",
                "type" => "browse",
                "route" => "all-cases",
                "objType" => "case",
                "icon" => "list-ul",
                "browseby" => "groups",
            )
        )
    )
);