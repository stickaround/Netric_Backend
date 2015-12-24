<?php
/**
 * Factory to create a new ConditionsMatchAcion
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\WorkFlow\Action;

use Netric\ServiceManager\ServiceLocatorInterface;

/**
 * Create a new CheckConditionAction
 */
class CheckConditionActionFactory
{
    /**
     * Create a new action based on a name
     *
     * @param ServiceLocatorInterface $serviceLocator For loading dependencies
     * @return ActionInterface
     */
    static public function create(ServiceLocatorInterface $serviceLocator)
    {
        // Return a new CheckConditionAction
        $entityLoader = $serviceLocator->get("EntityLoader");
        $queryIndex = $serviceLocator->get("EntityQuery_Index");
        $actionFactory = new ActionFactory($serviceLocator);
        return new CheckConditionAction($entityLoader, $actionFactory, $queryIndex);
    }
}
