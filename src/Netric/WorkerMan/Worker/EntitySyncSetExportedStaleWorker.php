<?php

declare(strict_types=1);

namespace Netric\WorkerMan\Worker;

use Netric\Account\AccountContainerFactory;
use Netric\Db\Relational\RelationalDbContainerFactory;
use Netric\WorkerMan\Job;
use Netric\WorkerMan\AbstractWorker;
use RuntimeException;

/**
 * This worker handles the marking of commit as stale for all sync collections
 */
class EntitySyncSetExportedStaleWorker extends AbstractWorker
{
    /**
     * Handle the marking of commit as stale for all sync collections
     *
     * @param Job $job
     * @return mixed The reversed string
     */
    public function work(Job $job)
    {
        $workload = $job->getWorkload();

        // Make sure the workload has everything we need
        if (!$this->validWorkload($workload)) {
            throw new RuntimeException("account_id, collection_type, last_commit_id, new_commit_id are all required");
        }

        // Example of getting the current working application
        $serviceManager = $this->getApplication()->getServiceManager();

        // Get the account
        $accountId = $workload['account_id'];
        $accountContainer = $serviceManager->get(AccountContainerFactory::class);
        $account = $accountContainer->loadById($accountId);
        $serviceManager = $account->getServiceManager();

        $databaseContainer = $serviceManager->get(RelationalDbContainerFactory::class);
        $database = $databaseContainer->getDbHandleForAccountId($accountId);

        $data = ["new_commit_id" => $workload['new_commit_id']];

        // Set previously exported commits as stale
        $database->update("entity_sync_export", $data, [
            "collection_type" => $workload['collection_type'],
            "commit_id" => $workload['last_commit_id']
        ]);

        // Set previously stale commits as even more stale
        $database->update("entity_sync_export", $data, [
            "collection_type" => $workload['collection_type'],
            "new_commit_id" => $workload['new_commit_id']
        ]);

        return true;
    }

    /**
     * Make sure the workload has all the params we need to do this job
     *
     * @param array $workload
     * @return bool true if the workload is complete
     */
    private function validWorkload(array $workload): bool
    {
        if (
            empty($workload['account_id']) ||
            empty($workload['collection_type']) ||
            empty($workload['last_commit_id']) ||
            empty($workload['new_commit_id'])
        ) {
            return false;
        }

        return true;
    }
}
