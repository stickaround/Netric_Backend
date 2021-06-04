<?php

/**
 * Factory to create a new ConditionsMatchAcion
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */

namespace Netric\Workflow\ActionExecutor;

use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Entity\EntityLoaderFactory;
use Netric\Workflow\DataMapper\WorkflowDataMapperFactory;

/**
 * Create a new WaitConditionAction
 */
class WaitConditionActionFactory
{
    /**
     * Create a new action based on a name
     *
     * @param ServiceContainerInterface $serviceLocator For loading dependencies
     * @return ActionInterface
     */
    public static function create(ServiceContainerInterface $serviceLocator)
    {
        // Return a new WaitConditionAction
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        $actionFactory = new ActionExecutorFactory($serviceLocator);
        $workFlowDataMapper = $serviceLocator->get(WorkflowDataMapperFactory::class);
        return new WaitConditionActionExecutor($entityLoader, $actionFactory, $workFlowDataMapper);
    }
}
