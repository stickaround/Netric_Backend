<?php
/**
 * Return navigation for entity of object type 'project'
 */
namespace modules\navigation;

return array(
    "title" => "Work",
    "icon" => "WorkIcon",
    "default_route" => "tasks",
    "navigation" => array(
        array(
            "title" => "Tasks",
            "type" => "browse",
            "route" => "tasks",
            "objType" => "task",
            "icon" => "ViewListIcon",
        ),
        array(
            "title" => "Projects",
            "type" => "browse",
            "route" => "projects",
            "objType" => "project",
            "icon" => "ViewListIcon",
        ),
        array(
            "title" => "Stories",
            "type" => "browse",
            "route" => "stories",
            "objType" => "project_story",
            "icon" => "ViewListIcon",
        ),
    )
);