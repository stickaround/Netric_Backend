<?php

/**
 * Return navigation for entity of object type 'files'
 */

namespace modules\navigation;

use Netric\Account\Module\LeftNavItemTypes;

return [
    "title" => "Files & Documents",
    "icon" => "PermMediaIcon",
    "default_route" => "all-files",
    "name" => "files",
    "short_title" => 'Files',
    "scope" => 'system',
    "sort_order" => '7',
    "f_system" => true,
    "navigation" => [
        [
            "title" => "All Files",
            "type" => LeftNavItemTypes::ENTITY_BROWSE,
            "route" => "all-files",
            "objType" => "file",
            "icon" => "ViewListIcon"
        ],
        [
            "title" => "All Folders",
            "type" => LeftNavItemTypes::ENTITY_BROWSE,
            "route" => "all-folders",
            "objType" => "folder",
            "icon" => "ViewListIcon"
        ]
    ]
];
