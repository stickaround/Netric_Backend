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
 * Create a new WaitConditionAction
 */
class WaitConditionActionFactory
{
    /**
     * Create a new action based on a name
     *
     * @param ServiceLocatorInterface $serviceLocator For loading dependencies
     * @return ActionInterface
     */
    static public function create(ServiceLocatorInterface $serviceLocator)
    {
        // Return a new WaitConditionAction
        $entityLoader = $serviceLocator->get("EntityLoader");
        $actionFactory = new ActionFactory($serviceLocator);
        $workFlowDataMapper = $serviceLocator->get("Netric/WorkFlow/DataMapper/DataMapper");
        return new WaitConditionAction($entityLoader, $actionFactory, $workFlowDataMapper);
    }
}
