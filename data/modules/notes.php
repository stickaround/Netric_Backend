<?php
/**
 * Return navigation for entity of object type 'notes'
 */
namespace modules\navigation;

return array(
    "title" => "Notes",
    "icon" => "LibraryBooksIcon",
    "default_route" => "all-notes",
    "name" => "notes",
    "short_title" => 'Notes',
    "scope" => 'system',
    "sort_order" => '8',
    "f_system" => true,
    "navigation" => array(
        array(
            "title" => "Manage Categories",
            "type" => "category",
            "route" => "manage-categories",
            "objType" => "note",
            "fieldName" => "groups",
            "icon" => "StyleIcon",
        ),
        array(
            "title" => "All Notes",
            "type" => "browse",
            "route" => "all-notes",
            "objType" => "note",
            "icon" => "ViewListIcon",
            "browseby" => "groups",
        )
    )
);