<?php

namespace Netric\WorkerMan\Worker;

use Netric\Log\LogInterface;
use Netric\Entity\EntityLoader;
use Netric\Entity\Notifier\Notifier;
use Netric\WorkerMan\Job;
use Netric\WorkerMan\WorkerInterface;

/**
 * This worker is used to send notifications about actions taken on entities
 */
class NotificationWorker implements WorkerInterface
{
    /**
     * Cache the result
     *
     * @var string
     */
    private $result = "";

    /**
     * Notifier service for sending notifications
     *
     * @var Notifier
     */
    private Notifier $notifier;

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
    public function __construct(Notifier $notifier, EntityLoader $entityLoader, LogInterface $log)
    {
        $this->notifier = $notifier;
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

        // Validate our workload
        if (!$this->isWorkloadValid($workload)) {
            return false;
        }

        // Get data from workload
        $user = $this->entityLoader->getEntityById($workload['user_id'], $workload['account_id']);
        $entity  = $this->entityLoader->getEntityById($workload['entity_id'], $workload['account_id']);

        if (!$user || !$entity) {
            $this->log->error("NotificationWorker->isWorkloadValid: Could not load user or entity");
            return false;
        }

        // Send the notification
        $this->notifier->send(
            $entity,
            $workload['event_name'],
            $user,
            $workload['changed_description']
        );

        // Set result and return it
        $this->result = true;
        return $this->result;
    }

    /**
     * Make sure the workload has all the required params
     */
    private function isWorkloadValid(array $workload): bool
    {
        if (empty($workload['account_id'])) {
            $this->log->error('NotificationWorker->isWorkloadValid: account_id is empty');
            return false;
        }

        if (empty($workload['entity_id'])) {
            $this->log->error('NotificationWorker->isWorkloadValid: entity_id is empty');
            return false;
        }

        if (empty($workload['user_id'])) {
            $this->log->error('NotificationWorker->isWorkloadValid: user_id is empty');
            return false;
        }

        if (empty($workload['event_name'])) {
            $this->log->error('NotificationWorker->isWorkloadValid: event_name is empty');
            return false;
        }

        if (empty($workload['changed_description'])) {
            $this->log->error('NotificationWorker->isWorkloadValid: changed_description is empty');
            return false;
        }

        // everthing looks good
        return true;
    }

    /**
     * Get the results of the last job
     *
     * @return string
     */
    public function getResult()
    {
        return $this->result;
    }
}
