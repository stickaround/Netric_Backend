<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\Worker;

use Netric\WorkerMan\Job;
use Netric\WorkerMan\AbstractWorker;

/**
 * This worker is used to synchronize changes with a mailbox
 */
class EmailMailboxSyncWorker extends AbstractWorker
{
    /**
     * Synchronize changes with a remote server
     *
     * @param Job $job
     * @return mixed The reversed string
     */
    public function work(Job $job)
    {
        $workload = $job->getWorkload();
        $application = $this->getApplication();
        $log = $application->getLog();

        $log->info("EmailMailboxSyncWorker->work: [STARTED]");

        /*
         * TODO: Copy code from /server/workers/email_account_sync:email_account_sync_mailbox
         */

        $log->info("EmailMailboxSyncWorker->work: [DONE]");

        return true;
    }
}
