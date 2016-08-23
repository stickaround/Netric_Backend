<?php
/**
 * Return navigation for entity of object type 'notes'
 */
namespace modules\navigation;

return array(
    "title" => "Notes",
    "icon" => "pencil-square-o",
    "default_route" => "all-notes",
    "navigation" => array(
        array(
            "title" => "Manage Categories",
            "type" => "category",
            "route" => "manage-categories",
            "objType" => "note",
            "fieldName" => "groups",
            "icon" => "tag",
        ),
        array(
            "title" => "New Note",
            "type" => "entity",
            "route" => "new-note",
            "objType" => "note",
            "icon" => "plus",
        ),
        array(
            "title" => "All Notes",
            "type" => "browse",
            "route" => "all-notes",
            "objType" => "note",
            "icon" => "tags",
            "browseby" => "groups",
        )
    )
);