<?php

namespace Netric\Entity\Notifier;

use Netric\Authentication\AuthenticationServiceFactory;
use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityQuery\Index\IndexFactory;

/**
 * Create a new Notifier service
 */
class NotifierFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceManager ServiceLocator for injecting dependencies
     * @return Notifier
     */
    public function createService(ServiceLocatorInterface $serviceManager)
    {
        // TODO: The below is causing a circular reference so we need to figure that out
        $entityLoader = $serviceManager->get(EntityLoaderFactory::class);
        $entityIndex = $serviceManager->get(IndexFactory::class);
        $authService = $serviceManager->get(AuthenticationServiceFactory::class);
        return new Notifier($authService, $entityLoader, $entityIndex);
    }
}
