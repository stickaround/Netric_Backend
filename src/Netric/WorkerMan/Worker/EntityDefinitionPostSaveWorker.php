<?php

declare(strict_types=1);

namespace Netric\WorkerMan\Worker;

use Netric\Account\AccountContainerFactory;
use Netric\WorkerMan\Job;
use Netric\WorkerMan\AbstractWorker;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use RuntimeException;

/**
 * This worker handles async actions to be carried out
 * after an entity definition has been saved.
 */
class EntityDefinitionPostSaveWorker extends AbstractWorker
{
    /**
     * Handle post-save actions
     *
     * @param Job $job
     * @return mixed The reversed string
     */
    public function work(Job $job)
    {
        $workload = $job->getWorkload();

        // Make sure the workload has everything we need
        if (!$this->validWorkload($workload)) {
            throw new RuntimeException("entity_definition_id, account_id, obj_type are all required");
        }

        // Example of getting the current working application
        $serviceManager = $this->getApplication()->getServiceManager();

        // Get the account
        $accountContainer = $serviceManager->get(AccountContainerFactory::class);
        $account = $accountContainer->loadById($workload['account_id']);
        $serviceManager = $account->getServiceManager();

        // Clear the cache
        $definitionLoader = $serviceManager->get(EntityDefinitionLoaderFactory::class);
        $definitionLoader->clearCache($workload['obj_type'], $account->getAccountId());
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
        if (empty($workload['entity_definition_id']) ||
            empty($workload['account_id']) ||
            empty($workload['obj_type'])
        ) {
            return false;
        }

        return true;
    }
}
