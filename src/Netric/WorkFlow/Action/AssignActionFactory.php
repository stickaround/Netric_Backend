<?php
namespace Netric\WorkFlow\Action;

use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\EntityLoaderFactory;

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
    static public function create(AccountServiceManagerInterface $serviceLocator)
    {
        // Return a new TestAction
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        $actionFactory = new ActionFactory($serviceLocator);
        $groupingsLoader = $serviceLocator->get("EntityGroupings_Loader");
        $queryIndex = $serviceLocator->get(IndexFactory::class);
        return new AssignAction($entityLoader, $actionFactory, $groupingsLoader, $queryIndex);
    }
}
