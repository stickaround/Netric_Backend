<?php

declare(strict_types=1);

namespace Netric\WorkerMan\Worker;

use Netric\Log\LogInterface;
use Netric\Entity\EntityLoader;
use Netric\Entity\Notifier\Notifier;
use Netric\WorkerMan\Job;
use Netric\WorkerMan\WorkerInterface;
use Netric\Workflow\WorkflowService;

/**
 * This worker is used to continue the execution of child actions of a wait action
 * after a period of time has passed
 */
class WorkflowWaitActionWorker implements WorkerInterface
{
    /**
     * Notifier service for sending notifications
     *
     * @var WorkflowService
     */
    private WorkflowService $workflowService;

    /**
     * Load up entities
     *
     * @var EntityLoader
     */
    private EntityLoader $entityLoader;

    /**
     * For logging of course
     *
     * @var LogInterface
     */
    private LogInterface $log;

    /**
     * Constructor.
     *
     * @param Notifier $notifier
     */
    public function __construct(WorkflowService $workflowService, EntityLoader $entityLoader, LogInterface $log)
    {
        $this->workflowService = $workflowService;
        $this->entityLoader = $entityLoader;
        $this->log = $log;
    }

    /**
     * Take a string and reverse it
     *
     * @param Job $job
     * @return bool true on scuess
     */
    public function work(Job $job)
    {
        $workload = $job->getWorkload();

        $this->log->error("WorkflowWaitActionWorker->work: starting for " . $workload['entity_id']);

        // Validate our workload
        if (!$this->isWorkloadValid($workload)) {
            $this->log->error("WorkflowWaitActionWorker->work: job not valid");
            return false;
        }

        // Get data from workload
        $user = $this->entityLoader->getEntityById($workload['user_id'], $workload['account_id']);
        $entity  = $this->entityLoader->getEntityById($workload['entity_id'], $workload['account_id']);
        $actionEntity = $this->entityLoader->getEntityById($workload['action_id'], $workload['account_id']);

        if (!$user || !$entity || !$actionEntity) {
            $this->log->error("WorkflowWaitActionWorker->work: Could not load user or entity");
            return false;
        }

        // Check if the entity was deleted since the workflow was staretd
        if ($entity->isArchived()) {
            // Stop the workflow gracefully
            $this->log->error("WorkflowWaitActionWorker->work: stopping workflow because entity was archived");
            return true;
        }

        // Resume workflow actions execution
        $this->workflowService->runChildActions($actionEntity, $entity, $user);

        // Set result and return it
        return true;
    }

    /**
     * Make sure the workload has all the required params
     */
    private function isWorkloadValid(array $workload): bool
    {

        if (empty($workload['account_id'])) {
            $this->log->error('WorkflowWaitActionWorker->isWorkloadValid: account_id is empty');
            return false;
        }

        if (empty($workload['entity_id'])) {
            $this->log->error('WorkflowWaitActionWorker->isWorkloadValid: entity_id is empty');
            return false;
        }

        if (empty($workload['user_id'])) {
            $this->log->error('WorkflowWaitActionWorker->isWorkloadValid: user_id is empty');
            return false;
        }

        if (empty($workload['action_id'])) {
            $this->log->error('WorkflowWaitActionWorker->isWorkloadValid: action_id is empty');
            return false;
        }


        // everthing looks good
        return true;
    }
}
