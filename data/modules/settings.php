<?php

/**
 * Return navigation for entity of object type 'settings'
 */

namespace modules\navigation;

use Netric\Account\Module\LeftNavItemTypes;

return [
    "title" => "Settings",
    "icon" => "SettingsIcon",
    "default_route" => "profile",
    "name" => "settings",
    "short_title" => 'Settings',
    "sort_order" => 20,
    "navigation" => [
        [
            "title" => "My Profile",
            "type" => LeftNavItemTypes::SETTINGS_PROFILE,
            "route" => "profile",
            "icon" => "AccountBoxIcon",
        ],
        // Removing until we can limit it from showing up in mobile applications
        // since the apple app store does not like that
        // [
        //     "title" => "Account & Billing",
        //     "type" => LeftNavItemTypes::SETTINGS_ACCOUNT_BILLING,
        //     "route" => "account_billing",
        //     "icon" => "CreditCardIcon",
        // ],
        // [
        //     "title" => "Modules",
        //     "type" => LeftNavItemTypes::SETTIGS_MODULES,
        //     "route" => "modules",
        //     "icon" => "ExtensionIcon",
        // ],
        // [
        //     "title" => "Entities",
        //     "type" => LeftNavItemTypes::SETTINGS_ENTITIES,
        //     "route" => "entities",
        //     "icon" => "TocIcon",
        // ],
        [
            "title" => "Automated Workflows",
            "type" => LeftNavItemTypes::ENTITY_BROWSE,
            "objType" => "workflow",
            "route" => "workflows",
            "icon" => "SettingsApplicationsIcon",
        ],
        [
            "title" => "Users",
            "type" => LeftNavItemTypes::ENTITY_BROWSE,
            "objType" => "user",
            "route" => "users",
            "icon" => "PersonIcon"
        ],
        [
            "title" => "User Teams",
            "type" => LeftNavItemTypes::ENTITY_BROWSE,
            "objType" => "user_team",
            "route" => "user-teams",
            "icon" => "GroupIcon"
        ]
    ]
];
