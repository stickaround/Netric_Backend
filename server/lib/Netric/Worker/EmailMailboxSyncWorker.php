<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\Worker;

use Netric\EntityQuery;
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

        // Make sure we have the required data
        if (
            !isset($workload['account_id']) ||
            !isset($workload['user_id']) ||
            !isset($workload['mailbox_id'])
        ) {
            $log->error(
                "EmailMailboxSyncWorker->work: fields required account_id, user_id, mailbox_id " .
                var_export($workload, true)
            );
            return false;
        }

        // Get the account we are working with
        $application = $this->getApplication();
        $account = $application->getAccount($workload['account_id']);
        $entityLoader = $account->getServiceManager()->get("EntityLoader");
        $entityIndex = $account->getServiceManager()->get("EntityQuery_Index");

        /*
        // Get email accounts
        $query = new EntityQuery("email_account");
        $query->where("owner_id")->equals($workload['user_id']);
        $query->andWhere("type")->doesNotEqual("");
        $results = $entityIndex->executeQuery($query);
        $num = $results->getTotalNum();
        for ($i = 0; $i < $num; $i++) {
            $emailAccount = $results->getEntity($i);
            $mailReceiver->syncMailbox($data['mailbox_id'], $emailAccount);
        }
        */

        /*
         * TODO: Copy code from /server/workers/email_account_sync:email_account_sync_mailbox
         */

        $log->info("EmailMailboxSyncWorker->work: [DONE]");

        return true;
    }
}
