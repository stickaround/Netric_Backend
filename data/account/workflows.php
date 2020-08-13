<?php

namespace data\account;

use Netric\Entity\ObjType\UserEntity;
use Netric\EntityQuery\Where;
use Netric\EntityDefinition\ObjectTypes;

return [
    [
        "name" => "New Task Assigned",
        "uname" => "new-task-sendmail",
        "notes" => "Sends an email to a task owner if someone else creates it",
        "obj_type" => ObjectTypes::TASK,
        "active" => true,
        "on_create" => true,
        "on_update" => true,
        "on_delete" => false,
        "on_daily" => false,
        "singleton" => false,
        "allow_manual" => false,
        "only_on_conditions_unmet" => true,
        "conditions" => [
            // Only trigger if the task is assinged to someone other than the current user
            [
                "blogic" => Where::COMBINED_BY_AND,
                "field_name" => "owner_id",
                "operator" => Where::OPERATOR_NOT_EQUAL_TO,
                "value" => UserEntity::USER_CURRENT,
            ]
        ],
        "actions" => [
            [
                "name" => "Send Email",
                "type" => "send_email",
                "params" => [
                    "from" => "no-reply@netric.com",
                    "subject" => "New Task",
                    "body" => "You have been assigned a new task: <%entity_link%>",
                    "to" => [
                        "<%owner_id.email%>"
                    ]
                ],
            ],
        ],
    ]
];
