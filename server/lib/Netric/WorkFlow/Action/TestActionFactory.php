<?php
/**
 * Factory to create a new TestAction
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\WorkFlow\Action;

use Netric\ServiceManager\ServiceLocatorInterface;

/**
 * Create a new TestAction
 */
class TestActionFactory
{
    /**
     * Create a new action based on a name
     *
     * @param ServiceLocatorInterface $serviceLocator For loading dependencies
     * @return ActionInterface
     */
    static public function create(ServiceLocatorInterface $serviceLocator)
    {
        // Return a new TestAction
        $entityLoader = $serviceLocator->get("EntityLoader");
        $actionFactory = new ActionFactory($serviceLocator);
        return new TestAction($entityLoader, $actionFactory);
    }
}
