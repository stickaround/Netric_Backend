<?php
/**
 * Return navigation for entity of object type 'project'
 */
namespace modules\navigation;

return array(
    "title" => "Work",
    "icon" => "WorkIcon",
    "default_route" => "tasks",
    "name" => "projects",
    "short_title" => 'Work',
    "scope" => 'system',
    "sort_order" => '6',
    "f_system" => true,
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
            "title" => "Issues",
            "type" => "browse",
            "route" => "stories",
            "objType" => "project_story",
            "icon" => "ViewListIcon",
        ),
    )
);