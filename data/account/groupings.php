<?php

use Netric\Entity\ObjType\TaskEntity;
use Netric\Entity\ObjType\UserEntity;

/**
 * Default groupings for entities
 *
 * These values will be added if and only if there are no (0] groupings for the
 * field. This allows an administrator to completely change their groupings without
 * it being overwritten with each update.
 */
return [
    'user' => [
        'groups' => [
            ['name' => UserEntity::GROUP_USERS, 'f_admin' => 'f'],
            ['name' => UserEntity::GROUP_EVERYONE, 'f_admin' => 'f'],
            ['name' => UserEntity::GROUP_CREATOROWNER, 'f_admin' => 'f'],
            ['name' => UserEntity::GROUP_ADMINISTRATORS, 'f_admin' => 't'],
        ],
    ],
    'task' => [
        'status_id' => [
            ["name" => TaskEntity::STATUS_TODO, "sort_oder" => 1, "color" => "2A4BD7"],
            ["name" => TaskEntity::STATUS_IN_PROGRESS, "sort_oder" => 2, "color" => "FF9233"],
            ["name" => TaskEntity::STATUS_IN_TEST, "sort_oder" => 3, "color" => "FFEE33"],
            ["name" => TaskEntity::STATUS_IN_REVIEW, "sort_oder" => 4, "color" => "575757"],
            ["name" => TaskEntity::STATUS_DEFERRED, "sort_oder" => 5, "color" => "1D6914"],
            ["name" => TaskEntity::STATUS_COMPLETED, "sort_oder" => 6, "color" => "1D6914"],
        ],
        'priority_id' => [
            ["name" => TaskEntity::PRIORITY_LOW, "sort_oder" => 1, "color" => "1D6914"],
            ["name" => TaskEntity::PRIORITY_MEDIUM, "sort_oder" => 2, "color" => "575757"],
            ["name" => TaskEntity::PRIORITY_HIGH, "sort_oder" => 3, "color" => "AD2323"],
        ],
        'type_id' => [
            ["name" => TaskEntity::TYPE_ENHANCEMENT, "color" => "1D6914"],
            ["name" => TaskEntity::TYPE_DEFECT, "color" => "AD2323"],
            ["name" => TaskEntity::TYPE_SUPPORT, "color" => "575757"],
        ]
    ],
    'marketing_campaign' => [
        'type_id' => [
            ["name" => "Email", "color" => "2A4BD7"],
            ["name" => "Advertisement", "color" => "575757"],
            ["name" => "Telephone", "color" => "FF9233"],
            ["name" => "Banner Ads", "color" => "FFEE33"],
            ["name" => "Public Relations", "color" => "1D6914"],
            ["name" => "Partners", "color" => "AD2323"],
            ["name" => "Resellers", "color" => "A0A0A0"],
            ["name" => "Referral Program", "color" => "814A19"],
            ["name" => "Direct Mail", "color" => "8126C0"],
            ["name" => "Trade Show", "color" => "9DAFFF"],
            ["name" => "Conference", "color" => "E9DEBB"],
            ["name" => "Other", "color" => "29D0D0"],

        ],
        'status_id' => [
            ["name" => "Planning", "sort_oder" => 1, "color" => "2A4BD7"],
            ["name" => "Active", "sort_oder" => 2, "color" => "575757"],
            ["name" => "Inactive", "sort_oder" => 3, "color" => "FF9233"],
            ["name" => "Complete", "sort_oder" => 4, "color" => "FFEE33"],
        ],
    ],
    'content_feed_post' => [
        'status_id' => [
            ["name" => "Draft", "color" => "2A4BD7"],
            ["name" => "Awaiting Review", "color" => "575757"],
            ["name" => "Rejected", "color" => "FF9233"],
            ["name" => "Published", "color" => "FFEE33"],
        ],
    ],
    'cms_page' => [
        'status_id' => [
            ["name" => "Draft", "color" => "2A4BD7"],
            ["name" => "Awaiting Review", "color" => "575757"],
            ["name" => "Rejected", "color" => "FF9233"],
            ["name" => "Published", "color" => "FFEE33"],
        ],
    ],
    'activity' => [
        'type_id' => [
            ["name" => "Phone Call", "color" => "2A4BD7"],
            ["name" => "Status Update", "color" => "575757"],
        ],
    ],
    'phone_call' => [
        'purpose_id' => [
            ["name" => "Prospecting", "color" => "2A4BD7"],
            ["name" => "Administrative", "color" => "FF9233"],
            ["name" => "Negotiation", "color" => "1D6914"],
            ["name" => "Demo", "color" => "AD2323"],
            ["name" => "Project", "color" => "1D6914"],
            ["name" => "Support", "color" => "AD2323"],
        ],
    ],
    'lead' => [
        'status_id' => [
            ["name" => "New: Not Contacted", "color" => "2A4BD7"],
            ["name" => "New: Pre Qualified", "color" => "9DAFFF"],
            ["name" => "Working: Attempted to Contact", "color" => "575757"],
            ["name" => "Working: Contacted", "color" => "FF9233"],
            ["name" => "Working: Contact Later", "color" => "FFEE33"],
            ["name" => "Closed: Converted", "color" => "1D6914"],
            ["name" => "Closed: Lost", "color" => "AD2323"],
            ["name" => "Closed: Junk", "color" => "29D0D0"],
        ],
        'rating_id' => [
            ["name" => "Hot", "color" => "2A4BD7"],
            ["name" => "Medium", "color" => "575757"],
            ["name" => "Cold", "color" => "FF9233"],
        ],
        'source_id' => [
            ["name" => "Advertisement", "color" => "2A4BD7"],
            ["name" => "Cold Call", "color" => "575757"],
            ["name" => "Employee Referral", "color" => "FF9233"],
            ["name" => "External Referral", "color" => "FFEE33"],
            ["name" => "Website", "color" => "1D6914"],
            ["name" => "Partner", "color" => "AD2323"],
            ["name" => "Email", "color" => "A0A0A0"],
            ["name" => "Web Research", "color" => "814A19"],
            ["name" => "Direct Mail", "color" => "8126C0"],
            ["name" => "Trade Show", "color" => "9DAFFF"],
            ["name" => "Conference", "color" => "E9DEBB"],
            ["name" => "Other", "color" => "29D0D0"],
        ],
    ],
    'opportunity' => [
        'stage_id' => [
            ["name" => "Qualification", "color" => "2A4BD7"],
            ["name" => "Needs Analysis", "color" => "575757"],
            ["name" => "Value Proposition", "color" => "FF9233"],
            ["name" => "Id. Decision Makers", "color" => "FFEE33"],
            ["name" => "Proposal/Price Quote", "color" => "1D6914"],
            ["name" => "Negotiation/Review", "color" => "AD2323"],
            ["name" => "Closed: Won", "color" => "A0A0A0"],
            ["name" => "Closed: Lost", "color" => "814A19"],
        ],
        'type_id' => [
            ["name" => "New Business", "color" => "2A4BD7"],
            ["name" => "Existing Business", "color" => "575757"],
        ],
        'objection_id' => [
            ["name" => "Not Interested / Don't need it", "color" => "2A4BD7"],
            ["name" => "Already Working with Someone", "color" => "575757"],
            ["name" => "Trouble Getting Approved", "color" => "FF9233"],
            ["name" => "Price Too High", "color" => "FFEE33"],
            ["name" => "Troubling Reputation", "color" => "1D6914"],
            ["name" => "Never Heard of Us", "color" => "AD2323"],
            ["name" => "Had Problems in the Past", "color" => "A0A0A0"],
            ["name" => "Too Confusing/Complex", "color" => "814A19"],
            ["name" => "Not a Good Fit", "color" => "8126C0"],
        ],
        'selling_point_id' => [
            ["name" => "Price", "color" => "2A4BD7"],

            ["name" => "Features", "color" => "575757"],
            ["name" => "Good Reputation", "color" => "FF9233"],
            ["name" => "Support", "color" => "FFEE33"],
            ["name" => "Simplicity", "color" => "1D6914"],
            ["name" => "Good Experience", "color" => "AD2323"],
        ],
    ],
];
