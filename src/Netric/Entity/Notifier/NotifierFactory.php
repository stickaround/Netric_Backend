<?php

namespace Netric\Entity\Notifier;

use Netric\Authentication\AuthenticationServiceFactory;
use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\ServiceManager\AccountServiceFactoryInterface;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityQuery\Index\IndexFactory;

/**
 * Create a new Notifier service
 */
class NotifierFactory implements AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param AccountServiceManagerInterface $serviceManager ServiceLocator for injecting dependencies
     * @return Notifier
     */
    public function createService(AccountServiceManagerInterface $serviceManager)
    {
        // TODO: The below is causing a circular reference so we need to figure that out
        $entityLoader = $serviceManager->get(EntityLoaderFactory::class);
        $entityIndex = $serviceManager->get(IndexFactory::class);
        $authService = $serviceManager->get(AuthenticationServiceFactory::class);
        return new Notifier($authService, $entityLoader, $entityIndex);
    }
}
