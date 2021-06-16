<?php

namespace Netric\Workflow\ActionExecutor;

use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Entity\EntityLoaderFactory;
use Netric\Workflow\WorkFlowLegacyManagerFactory;

/**
 * Factory to create a new StartWorkflowAction
 */
class StartWorkflowActionFactory
{
    /**
     * Construct new action
     *
     * @param ServiceLocatorInterface $serviceLocator For loading dependencies
     * @return ActionInterface
     */
    public static function create(ServiceLocatorInterface $serviceLocator)
    {
        // Return a new TestAction
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        $actionFactory = new ActionExecutorFactory($serviceLocator);
        $workflowManager = $serviceLocator->get(WorkFlowLegacyManagerFactory::class);
        return new StartWorkflowActionExecutor($entityLoader, $actionFactory, $workflowManager);
    }
}
