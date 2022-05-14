<?php

declare(strict_types=1);

namespace Netric\WorkerMan\Worker;

use Netric\Account\AccountContainerFactory;
use Netric\Entity\ActivityLogFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\WorkerMan\Job;
use Netric\WorkerMan\AbstractWorker;
use Netric\EntityDefinition\ObjectTypes;
use Netric\Log\LogFactory;
use Netric\WorkerMan\WorkerServiceFactory;
use Netric\Workflow\WorkflowServiceFactory;
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

        // Handle deleted/deactivated account
        if (!$account) {
            $log->info(__CLASS__ . ': worker exiting gracefully because account is deleted');
            return true;
        }

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

        // Avoid worker jobs which is kind of circular
        if ($entity->getDefinition()->getObjType() === ObjectTypes::WORKER_JOB) {
            return true;
        }

        // Save data to EntityQuery Index
        $entityIndex = $serviceManager->get(IndexFactory::class);
        $entityIndex->save($entity);

        // Create or send notifications if the changelog was sent
        // We exclude worker jobs because right now the scheduler creates a new entity of type worker_job
        // which would cause an infinite loop. When we switch over to a better job queue with scheduling
        // this will become cleaner - and faster too
        if (
            !empty($workload['changed_description']) && $user &&
            isset($workload['log_activity']) && $workload['log_activity'] === true
        ) {
            // Send in 3 seconds to give the UI time to register if the entity is alraedy seen
            // before spamming them with notifications
            $workerService = $serviceManager->get(WorkerServiceFactory::class);
            $workerService->doWorkDelayed(
                NotificationWorker::class,
                [
                    'account_id' => $account->getAccountId(),
                    'entity_id' => $entity->getEntityId(),
                    'user_id' => $user->getEntityid(),
                    'event_name' => $workload['event_name'],
                    'changed_description' => $workload['changed_description']
                ],
                3 // Delay for 3 seconds
            );
        }

        // Log the activity if log_activity was set to true
        if ($user && isset($workload['log_activity']) && $workload['log_activity'] === true) {
            $activityLog = $serviceManager->get(ActivityLogFactory::class);
            $activityLog->log($user, $workload['event_name'], $entity);
        }

        // Launch workflows
        if ($user) {
            $workflowService = $serviceManager->get(WorkflowServiceFactory::class);
            $workflowService->runWorkflowsOnEvent($entity, $workload['event_name'], $user);
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
