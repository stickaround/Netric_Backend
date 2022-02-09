<?php

/**
 * Return navigation for entity of object type 'infocenter'
 *
 * browse-leftnav is a new navigation type where it will display the list of entities in the left navigation
 */

namespace modules\navigation;

use Netric\Account\Module\LeftNavItemTypes;
use Netric\EntityDefinition\ObjectTypes;

return [
    "title" => "Document",
    "icon" => "AssignmentIcon",
    "default_route" => "my-notes",
    "name" => "knowledge",
    "short_title" => 'Document',
    "scope" => 'system',
    "sort_order" => 3,
    "f_system" => true,
    "navigation" => [
        [
            "objType" => ObjectTypes::NOTE,
            "type" => LeftNavItemTypes::ENTITY_BROWSE,
            "title" => "My Notes",
            "route" => "my-notes",
            "icon" => "AssignmentIcon",
            "browseby" => "groups",
        ],
        [
            "objType" => ObjectTypes::DOCUMENT,
            "type" => LeftNavItemTypes::HEADER,
            "subtype" => LeftNavItemTypes::ENTITY,
            "title" => "Spaces",
            "route" => "newspace",
            "icon" => "AddIcon",
            "helptext" => "Space will flag a document as top-level document and will be displayed in the left navigation as clickable link.",
            "data" => [
                "is_rootspace" => true,
            ],
        ],
        [
            "objType" => ObjectTypes::DOCUMENT,
            "type" => LeftNavItemTypes::ENTITY_BROWSE_LEFTNAV,
            "route" => "spaces",
            "browser_view" => "spaces",
        ],
        [
            "objType" => ObjectTypes::DOCUMENT,
            "type" => LeftNavItemTypes::ENTITY_BROWSE,
            "title" => "All Documents",
            "route" => "spaces",
            "icon" => "SearchIcon",
        ],
    ],
];
