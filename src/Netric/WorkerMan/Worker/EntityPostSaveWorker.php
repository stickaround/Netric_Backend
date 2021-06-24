<?php

declare(strict_types=1);

namespace Netric\WorkerMan\Worker;

use Netric\Account\AccountContainerFactory;
use Netric\Entity\ActivityLogFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\WorkerMan\Job;
use Netric\WorkerMan\AbstractWorker;
use Netric\Entity\Notifier\NotifierFactory;
use Netric\EntityDefinition\ObjectTypes;
use Netric\Log\LogFactory;
use RuntimeException;

/**
 * This worker handles async actions to be carried out
 * after an entity has been saved.
 */
class EntityPostSaveWorker extends AbstractWorker
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
            throw new RuntimeException("entity_id, account_id, and user_id are all required");
        }

        $serviceManager = $this->getApplication()->getServiceManager();

        // Log it
        $log = $serviceManager->get(LogFactory::class);
        $log->info(__CLASS__ . ': worker processing job for ' . $workload['entity_id']);

        // Get the account
        $accountContainer = $serviceManager->get(AccountContainerFactory::class);
        $account = $accountContainer->loadById($workload['account_id']);
        $serviceManager = $account->getServiceManager();

        // Get the user
        $entityLoader = $serviceManager->get(EntityLoaderFactory::class);
        $user = $entityLoader->getEntityById($workload['user_id'], $account->getAccountId());

        // Now get the entity we changed
        $entity = $entityLoader->getEntityById($workload['entity_id'], $account->getAccountId());

        // Check if the entity was deleted
        if (!$entity) {
            return true;
        }

        // Save data to EntityQuery Index
        $entityIndex = $serviceManager->get(IndexFactory::class);
        $entityIndex->save($entity);

        // Create or send notifications if the changelog was sent
        if (!empty($workload['changed_description']) && $user) {
            $notifierService = $serviceManager->get(NotifierFactory::class);
            $notifierService->send($entity, $workload['event_name'], $user, $workload['changed_description'], $log);
        }

        // Log the activity
        $activityLog = $serviceManager->get(ActivityLogFactory::class);
        $activityLog->log($user, $workload['event_name'], $entity);

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
            empty($workload['user_id']) ||
            empty($workload['entity_id']) ||
            empty($workload['event_name'])
        ) {
            return false;
        }

        return true;
    }
}
