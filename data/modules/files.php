<?php
/**
 * Return navigation for entity of object type 'files'
 */
namespace modules\navigation;

return array(
    "title" => "Files & Documents",
    "icon" => "PermMediaIcon",
    "default_route" => "all-files",
    "name" => "files",
    "short_title" => 'Files',
    "scope" => 'system',
    "sort_order" => '7',
    "f_system" => true,
    "navigation" => array(
        array(
            "title" => "All Files",
            "type" => "browse",
            "route" => "all-files",
            "objType" => "file",
            "icon" => "ViewListIcon"
        ),
        array(
            "title" => "All Folders",
            "type" => "browse",
            "route" => "all-folders",
            "objType" => "folder",
            "icon" => "ViewListIcon"
        )
    )
);