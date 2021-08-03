<?php

namespace modules\navigation;

use Netric\Account\Module\LeftNavItemTypes;

/**
 * Return navigation for entity of object type 'home'
 */

return [
    "title" => "Home",
    "icon" => "HomeIcon",
    "default_route" => "feed",
    "name" => "home",
    "short_title" => 'Home',
    "scope" => 'system',
    "sort_order" => '1',
    "settings" => "none",
    "f_system" => true,
    "navigation" => [
        [
            "title" => "Feed",
            "type" => LeftNavItemTypes::HOME_FEED,
            "route" => "feed",
            "icon" => "ViewAgendaIcon"
        ],
        [
            "title" => "Notifications",
            "type" => LeftNavItemTypes::ENTITY_BROWSE,
            "route" => "notifications",
            "objType" => "notification",
            "icon" => "NotificationsIcon"
        ]
    ]
];
