<?php

declare(strict_types=1);

namespace Netric\WorkerMan\Worker;

use Netric\Account\AccountContainerFactory;
use Netric\Db\Relational\RelationalDbContainerFactory;
use Netric\WorkerMan\Job;
use Netric\WorkerMan\AbstractWorker;
use RuntimeException;

/**
 * This worker handles then logging of exported entities
 */
class EntitySyncLogExportedWorker extends AbstractWorker
{
    /**
     * Handle the log that a commit was exported from the collection
     *
     * @param Job $job
     * @return mixed The reversed string
     */
    public function work(Job $job)
    {
        $workload = $job->getWorkload();

        // Make sure the workload has everything we need
        if (!$this->validWorkload($workload)) {
            throw new RuntimeException("collection_id, unique_id, collection_type, account_id are all required");
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

        $whereParams = [
          "collection_id" => $workload["collection_id"],
          "unique_id" => $workload["unique_id"]
        ];

        $sql = "SELECT unique_id FROM entity_sync_export
                WHERE collection_id=:collection_id AND unique_id=:unique_id";

        $result = $database->query($sql, $whereParams);

        if ($result->rowCount()) {
            if (isset($workload["commit_id"])) {
                $updateData = ["commit_id" => $workload["commit_id"], "new_commit_id" => null];
                $database->update("entity_sync_export", $updateData, $whereParams);
            } else {
                $database->delete("entity_sync_export", $whereParams);
            }
        } elseif (isset($workload["commit_id"])) {
            $insertData = array_merge([
                "collection_type" => $workload["collection_type"],
                "commit_id" => $workload["commit_id"]
            ], $whereParams);

            $database->insert("entity_sync_export", $insertData);
        }

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
        if (empty($workload['collection_id']) ||
            empty($workload['collection_type']) ||
            empty($workload['unique_id']) ||
            empty($workload['account_id'])
        ) {
            return false;
        }

        return true;
    }
}
