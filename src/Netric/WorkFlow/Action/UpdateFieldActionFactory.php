<?php

/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\WorkFlow\Action;

use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\Entity\EntityLoaderFactory;

/**
 * Factory to create a new UpdateFieldAction
 */
class UpdateFieldActionFactory
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
        $actionFactory = new ActionFactory($serviceLocator);
        return new UpdateFieldAction($entityLoader, $actionFactory);
    }
}
