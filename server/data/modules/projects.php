<?php
/**
 * Return navigation for entity of object type 'project'
 */
namespace modules\navigation;

return array(
    "title" => "Work",
    "icon" => "check-square-o",
    "default_route" => "all-tasks",
    "navigation" => array(
        array(
            "title" => "New Task",
            "type" => "entity",
            "route" => "new-task",
            "objType" => "task",
            "icon" => "AddIcon",
        ),
        array(
            "title" => "All Tasks",
            "type" => "browse",
            "route" => "all-tasks",
            "objType" => "task",
            "icon" => "ViewListIcon",
            "browseby" => "groups",
        ),
        array(
            "title" => "New Project",
            "type" => "entity",
            "route" => "new-project",
            "objType" => "project",
            "icon" => "AddIcon",
        ),
        array(
            "title" => "All Projects",
            "type" => "browse",
            "route" => "all-projects",
            "objType" => "project",
            "icon" => "ViewListIcon",
            "browseby" => "groups",
        )
    )
);