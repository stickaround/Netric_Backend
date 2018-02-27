<?php
/**
 * Return navigation for entity of object type 'settings'
 */
namespace modules\navigation;

return array(
    "title" => "Settings",
    "icon" => "SettingsIcon",
    "default_route" => "profile",
    "navigation" => array(
        array(
            "title" => "My Profile",
            "type" => "plugin",
            "route" => "profile",
            "plugin" => "Profile",
            "icon" => "AccountProfileIcon",
        ),
        array(
            "title" => "Modules",
            "type" => "plugin",
            "route" => "modules",
            "plugin" => "Modules",
            "icon" => "ExtensionIcon",
        ),
        array(
            "title" => "Entities",
            "type" => "plugin",
            "route" => "entities",
            "plugin" => "Entities",
            "icon" => "TocIcon",
        ),
        array(
            "title" => "Automated Workflows",
            "type" => "browse",
            "objType" => "workflow",
            "route" => "workflows",
            "icon" => "SettingsApplicationIcon",
        ),
        array(
            "title" => "Users",
            "type" => "browse",
            "objType" => "user",
            "route" => "users",
            "icon" => "GroupIcon"
        ),
        array(
            "title" => "User Teams",
            "type" => "browse",
            "objType" => "user_teams",
            "route" => "user-teams",
            "icon" => "StreetViewIcon"
        )
    )
);