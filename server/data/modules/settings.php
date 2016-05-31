<?php
/**
 * Return navigation for entity of object type 'settings'
 */
namespace modules\navigation;

return array(
    "title" => "Settings",
    "icon" => "wrench",
    "default_route" => "workflows",
    "navigation" => array(
        array(
            "title" => "Automated Workflows",
            "type" => "browse",
            "objType" => "workflow",
            "route" => "workflows",
            "icon" => "cogs",
        ),
        array(
            "title" => "New User",
            "type" => "entity",
            "route" => "new-user",
            "objType" => "user",
            "icon" => "user-plus",
        ),
        array(
            "title" => "Users",
            "type" => "browse",
            "objType" => "user",
            "route" => "users",
            "icon" => "users"
        )
    )
);