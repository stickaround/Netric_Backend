<?php
/**
 * Return navigation for entity of object type 'files'
 */
namespace modules\navigation;

return array(
    "title" => "Files & Documents",
    "icon" => "PermMediaIcon",
    "default_route" => "all-files",
    "navigation" => array(
        array(
            "title" => "All Files",
            "type" => "browse",
            "route" => "all-files",
            "objType" => "file",
            "icon" => "ViewListIcon",
            "browseby" => "groups",
        )
    )
);