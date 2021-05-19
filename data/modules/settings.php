<?php

/**
 * Return navigation for entity of object type 'settings'
 */

namespace modules\navigation;

return [
    "title" => "Settings",
    "icon" => "SettingsIcon",
    "default_route" => "profile",
    "name" => "settings",
    "short_title" => 'Settings',
    "sort_order" => 11,
    "navigation" => [
        [
            "title" => "My Profile",
            "type" => "plugin",
            "route" => "profile",
            "plugin" => "SettingsProfile",
            "icon" => "AccountProfileIcon",
        ],
        [
            "title" => "Account & Billing",
            "type" => "plugin",
            "route" => "account_billing",
            "plugin" => "SettingsAccountBilling",
            "icon" => "AccountBoxIcon",
        ],
        [
            "title" => "Modules",
            "type" => "plugin",
            "route" => "modules",
            "plugin" => "SettingsModules",
            "icon" => "ExtensionIcon",
        ],
        [
            "title" => "Entities",
            "type" => "plugin",
            "route" => "entities",
            "plugin" => "SettingsEntities",
            "icon" => "TocIcon",
        ],
        [
            "title" => "Automated Workflows",
            "type" => "browse",
            "objType" => "workflow",
            "route" => "workflows",
            "icon" => "SettingsApplicationsIcon",
        ],
        [
            "title" => "Users",
            "type" => "browse",
            "objType" => "user",
            "route" => "users",
            "icon" => "GroupIcon"
        ],
        [
            "title" => "User Teams",
            "type" => "browse",
            "objType" => "user_team",
            "route" => "user-teams",
            "icon" => "StreetViewIcon"
        ]
    ]
];
