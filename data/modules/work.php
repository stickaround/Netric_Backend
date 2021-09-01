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
    "icon" => "WorkIcon",
    "default_route" => "my-task",
    "name" => "work",
    "short_title" => 'Work',
    "scope" => 'system',
    "sort_order" => '6',
    "f_system" => true,
    "navigation" => [
        [
            "title" => "Work Home",
            "type" => LeftNavItemTypes::WORK_HOME,
            "route" => "home",
            "objType" => ObjectTypes::TASK,
            "icon" => "ViewAgendaIcon"
        ],
        [
            "title" => "New Task",
            "type" => LeftNavItemTypes::LINK,
            "route" => "browse/" . ObjectTypes::TASK . "/new",
            "objType" => ObjectTypes::TASK,
            "icon" => "AddBoxIcon",
        ],
        [
            "title" => "My Tasks",
            "type" => LeftNavItemTypes::ENTITY_BROWSE,
            "browser_view" => "my_task",
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
            "browser_view" => "my_task",
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
