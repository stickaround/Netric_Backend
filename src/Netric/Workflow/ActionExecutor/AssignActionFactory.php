<?php

namespace Netric\Workflow\ActionExecutor;

use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityGroupings\GroupingLoaderFactory;

/**
 * Factory to create a new AssignAction
 */
class AssignActionFactory
{
    /**
     * Construct new action
     *
     * @param AccountServiceManagerInterface $serviceLocator For loading dependencies
     * @return ActionInterface
     */
    public static function create(AccountServiceManagerInterface $serviceLocator)
    {
        // Return a new TestAction
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        $actionFactory = new ActionExecutorFactory($serviceLocator);
        $groupingsLoader = $serviceLocator->get(GroupingLoaderFactory::class);
        $queryIndex = $serviceLocator->get(IndexFactory::class);
        return new AssignActionExecutor($entityLoader, $actionFactory, $groupingsLoader, $queryIndex);
    }
}
