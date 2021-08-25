<?php

/**
 * Return navigation for entity of object type 'infocenter'
 *
 * browse-leftnav is a new navigation type where it will display the list of entities in the left navigation
 */

namespace modules\navigation;

use Netric\Account\Module\LeftNavItemTypes;

return [
    "title" => "Document",
    "icon" => "LightbulbOutlineIcon",
    "default_route" => "my-notes",
    "name" => "knowledge",
    "short_title" => 'Docs',
    "scope" => 'system',
    "sort_order" => '10',
    "f_system" => true,
    "navigation" => [
        [
            "title" => "My Notes",
            "type" => LeftNavItemTypes::ENTITY_BROWSE,
            "route" => "my-notes",
            "objType" => "note",
            "icon" => "AssignmentIcon",
        ],
        [
            "type" => LeftNavItemTypes::HEADER,
            "title" => "Spaces",
            "route" => "spaces",
        ],
        [
            "type" => LeftNavItemTypes::ENTITY_BROWSE_LEFTNAV,
            "route" => "spaces",
            "browser_view" => "spaces",
            "objType" => "infocenter_document"
        ],
        [
            "title" => "All Documents",
            "type" => LeftNavItemTypes::ENTITY_BROWSE,
            "route" => "spaces",
            "objType" => "infocenter_document",
            "icon" => "SearchIcon",
        ],
    ],
];
