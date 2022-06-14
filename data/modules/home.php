<?php

namespace modules\navigation;

use Netric\Account\Module\LeftNavItemTypes;
use Netric\EntityDefinition\ObjectTypes;

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
    "sort_order" => 1,
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
            "objType" => ObjectTypes::NOTIFICATION,
            "icon" => "NotificationsIcon"
        ],
        [
            "title" => "People",
            "type" => LeftNavItemTypes::ENTITY_BROWSE,
            "route" => "people",
            "objType" => ObjectTypes::USER,
            'browserView' => 'active',
            "icon" => "PeopleIcon"
        ],
        [
            "type" => LeftNavItemTypes::HEADER,
            "title" => "Recently Viewed",
            "route" => "recent",
        ],
        [
            "type" => LeftNavItemTypes::RECENT_ENTITIES,
            "route" => "recent",
        ],
    ]
];
