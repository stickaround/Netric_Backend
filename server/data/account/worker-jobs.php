<?php
/**
 * Worker jobs (usually recurring) that should exist for every account
 */
namespace data\account;

use Netric\Entity\Recurrence\RecurrencePattern;

/*
 * Set any recurring work that each account needs to execute here
 * and it will be applied every time we run update on the account 
 * which usually takes place when we deploy code
 * 
 * @see /bin/scripts/update/always/06-worker-jobs.php to see how these
 * are applied to each account.
 */
return [
    [
        'worker_name' =>'EntityMaintainer',
        'job_data'=> [],
        'recurrence' => [
            'type' => RecurrencePattern::RECUR_DAILY,
            'interval' => 1
        ] 
    ]
];