<?php

declare(strict_types=1);

namespace Netric\Workflow;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Log\LogFactory;
use Netric\Workflow\ActionExecutorFactory;
use Netric\Workflow\DataMapper\WorkflowDataMapperFactory;

/**
 * Handle setting up a worker service
 */
class WorkflowServiceFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return WorkerService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $workflowDataMapper = $serviceLocator->get(WorkflowDataMapperFactory::class);
        $log = $serviceLocator->get(LogFactory::class);
        $actionExecutorFactory = new ActionExecutorFactory($serviceLocator);
        return new WorkflowService($workflowDataMapper, $actionExecutorFactory, $log);
    }
}
