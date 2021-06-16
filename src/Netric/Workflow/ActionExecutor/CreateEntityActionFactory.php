<?php

namespace Netric\Workflow\ActionExecutor;

use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Entity\EntityLoaderFactory;

/**
 * Factory to create a new CreateEntityAction
 */
class CreateEntityActionFactory
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
        return new CreateEntityActionExecutor($entityLoader, $actionFactory);
    }
}
