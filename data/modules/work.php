<?php

namespace modules\navigation;

use Netric\EntityDefinition\ObjectTypes;
use Netric\Account\Module\LeftNavItemTypes;

/**
 * Return navigation for entity of work module
 *
 * The browser_view property in navigation refers to the views that is set in
 * objType's browser views (e.g. server/data/browser_views/task.php)
 */

return [
    "title" => "Work",
    "icon" => "CheckBoxIcon",
    "default_route" => "home",
    "name" => "work",
    "short_title" => 'Work',
    "scope" => 'system',
    "sort_order" => 4,
    "f_system" => true,
    "navigation" => [

        [
            "type" => LeftNavItemTypes::ENTITY,
            "title" => "New Task",
            "route" => "newtask",
            "icon" => "AddBoxIcon",
            "objType" => ObjectTypes::TASK,
        ],
        [
            "title" => "Work Home",
            "type" => LeftNavItemTypes::WORK_HOME,
            "route" => "home",
            "objType" => ObjectTypes::TASK,
            "icon" => "DashboardIcon"
        ],
        [
            "title" => "My Tasks",
            "type" => LeftNavItemTypes::ENTITY_BROWSE,
            "browser_view" => "my_tasks",
            "route" => "my-task",
            "objType" => ObjectTypes::TASK,
            "icon" => "CheckIcon",
        ],
        [
            "title" => "Delegated Tasks",
            "type" => LeftNavItemTypes::ENTITY_BROWSE,
            "browser_view" => "tasks_i_have_assigned",
            "route" => "delegated-task",
            "objType" => ObjectTypes::TASK,
            "icon" => "AssignmentIndIcon",
        ],
        [
            "title" => "Unassigned Tasks",
            "type" => LeftNavItemTypes::ENTITY_BROWSE,
            "browser_view" => "unassigned_incomplete",
            "route" => "unassigned",
            "objType" => ObjectTypes::TASK,
            "icon" => "OutlineNoAccountsIcon",
        ],
        [
            "title" => "All Tasks",
            "type" => LeftNavItemTypes::ENTITY_BROWSE,
            "browser_view" => "all_tasks",
            "route" => "all-task",
            "objType" => ObjectTypes::TASK,
            "icon" => "DoneAllIcon",
        ],
        [
            "title" => "Task Board",
            "type" => LeftNavItemTypes::TASK_BOARD,
            "browser_view" => "my_tasks",
            "route" => "board-view",
            "objType" => ObjectTypes::TASK,
            "icon" => "ViewColumnIcon",
        ],
        [
            "type" => LeftNavItemTypes::HEADER,
            "title" => "Projects",
            "route" => "projects"
        ],
        [
            "type" => LeftNavItemTypes::ENTITY_BROWSE_LEFTNAV,
            "route" => "projects",
            "browser_view" => "my_open_projects",
            "objType" => ObjectTypes::PROJECT,
            "icon" => "DoneAllIcon",
        ],
        [
            "title" => "All Projects",
            "type" => LeftNavItemTypes::ENTITY_BROWSE,
            "route" => "projects",
            "objType" => ObjectTypes::PROJECT,
            "icon" => "SearchIcon",
        ]
    ]
];
