<?php

declare(strict_types=1);

namespace Netric\WorkerMan\Worker;

use Netric\Account\AccountContainerFactory;
use Netric\Db\Relational\RelationalDbContainerFactory;
use Netric\WorkerMan\Job;
use Netric\WorkerMan\AbstractWorker;
use RuntimeException;

/**
 * This worker handles then logging of imported entities
 */
class EntitySyncLogImportedWorker extends AbstractWorker
{
    /**
     * Handle the log that a commit was imported from the collection
     *
     * @param Job $job
     * @return mixed The reversed string
     */
    public function work(Job $job)
    {
        $workload = $job->getWorkload();

        // Make sure the workload has everything we need
        if (!$this->validWorkload($workload)) {
            throw new RuntimeException("collection_id, unique_id, account_id are all required");
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

        $whereData = [
            "collection_id" => $workload["collection_id"],
            "unique_id" => $workload["unique_id"]
        ];

        if (isset($workload["object_id"])) {
          $syncData = [
              "object_id" => $workload["object_id"],
              "revision" => isset($workload["revision"]) ? $workload["revision"] :  null,
              "remote_revision" => isset($workload["remote_revision"]) ? $workload["remote_revision"] :  null
          ];

          $sql = "SELECT unique_id FROM entity_sync_import
                  WHERE collection_id=:collection_id AND unique_id=:unique_id";

          $result = $database->query($sql, $whereData);

          if ($result->rowCount()) {
              $database->update("entity_sync_import", $syncData, $whereData);
          } else {
              $database->insert("entity_sync_import", array_merge($syncData, $whereData));
          }
        } else {
            /*
            * If we have no localId then that means the import is no longer part of the local store
            * and has not been imported so delete the log entry.
            */
            // $database->delete("entity_sync_import", $whereData);
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
        if (
            empty($workload['collection_id']) ||
            empty($workload['unique_id']) ||
            empty($workload['account_id'])
        ) {
            return false;
        }

        return true;
    }
}
