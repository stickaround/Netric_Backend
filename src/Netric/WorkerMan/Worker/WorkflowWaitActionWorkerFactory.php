<?php

declare(strict_types=1);

namespace Netric\WorkerMan\Worker;

use Netric\Entity\EntityLoader;
use Netric\Log\LogFactory;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Workflow\WorkflowService;

/**
 * Serivce factory for WorkflowWaitActionWorker
 */
class WorkflowWaitActionWorkerFactory
{
    /**
     * Construct service with dependencies
     *
     * @param ServiceLocatorInterface $serviceLocator For injecting dependencies
     * @return NotificationWorker
     */
    public function create(ServiceLocatorInterface $serviceLocator)
    {
        $workflowService = $serviceLocator->get(WorkflowService::class);
        $entityLoader = $serviceLocator->get(EntityLoader::class);
        $log = $serviceLocator->get(LogFactory::class);
        return new WorkflowWaitActionWorker($workflowService, $entityLoader, $log);
    }
}
