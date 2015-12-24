<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\WorkFlow\Action;

use Netric\ServiceManager\ServiceLocatorInterface;

/**
 * Factory to create a new WebhookAction
 */
class WebhookActionFactory
{
    /**
     * Construct new action
     *
     * @param ServiceLocatorInterface $serviceLocator For loading dependencies
     * @return ActionInterface
     */
    static public function create(ServiceLocatorInterface $serviceLocator)
    {
        // Return a new TestAction
        $entityLoader = $serviceLocator->get("EntityLoader");
        $actionFactory = new ActionFactory($serviceLocator);
        return new WebhookAction($entityLoader, $actionFactory);
    }
}
