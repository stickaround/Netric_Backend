<?php

/**
 * Return navigation for entity of work module
 * 
 * Note: 
 * The browser_view property in navigation refers to the views that is set in objType's browser views (e.g. server/data/browser_views/task.php)
 */

namespace modules\navigation;

return array(
    "title" => "Work",
    "icon" => "WorkIcon",
    "default_route" => "tasks",
    "name" => "work",
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
            "icon" => "ViewListIcon"
        ),
        array(
            "title" => "My Task",
            "type" => "browse",
            "browser_view" => "my_task",
            "route" => "my-task",
            "objType" => "task",
            "icon" => "ChevronRightIcon",
        ),
        array(
            "title" => "All Tasks",
            "type" => "browse",
            "browser_view" => "all_tasks",
            "route" => "all-task",
            "objType" => "task",
            "icon" => "ChevronRightIcon",
        ),
        array(
            "title" => "Projects",
            "type" => "browse",
            "route" => "projects",
            "objType" => "project",
            "icon" => "ViewListIcon",
        )
    )
);
