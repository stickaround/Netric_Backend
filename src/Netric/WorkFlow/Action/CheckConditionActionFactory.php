<?php
namespace Netric\WorkFlow\Action;

use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityQuery\Index\IndexFactory;

/**
 * Create a new CheckConditionAction
 */
class CheckConditionActionFactory
{
    /**
     * Create a new action based on a name
     *
     * @param AccountServiceManagerInterface $serviceLocator For loading dependencies
     * @return ActionInterface
     */
    public static function create(AccountServiceManagerInterface $serviceLocator)
    {
        // Return a new CheckConditionAction
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        $queryIndex = $serviceLocator->get(IndexFactory::class);
        $actionFactory = new ActionFactory($serviceLocator);
        return new CheckConditionAction($entityLoader, $actionFactory, $queryIndex);
    }
}