<?php

/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */

namespace Netric\Workflow\ActionExecutor;

use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Mail\SenderServiceFactory;
use Netric\Entity\EntityLoaderFactory;

/**
 * Factory to create a new SendEmailAction
 */
class SendEmailActionFactory
{
    /**
     * Construct new action
     *
     * @param ServiceContainerInterface $serviceLocator For loading dependencies
     * @return ActionInterface
     */
    public static function create(ServiceContainerInterface $serviceLocator)
    {
        // Return a new TestAction
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        $actionFactory = new ActionExecutorFactory($serviceLocator);
        $senderService = $serviceLocator->get(SenderServiceFactory::class);
        return new SendEmailActionExecutor($entityLoader, $actionFactory, $senderService);
    }
}
