<?php

namespace Netric\Entity\Notifier;

use Netric\Authentication\AuthenticationServiceFactory;
use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityQuery\Index\IndexFactory;

/**
 * Create a new Notifier service
 */
class NotifierFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface $serviceManager ServiceLocator for injecting dependencies
     * @return Notifier
     */
    public function __invoke(ServiceContainerInterface $serviceManager)
    {
        $entityLoader = $serviceManager->get(EntityLoaderFactory::class);
        $entityIndex = $serviceManager->get(IndexFactory::class);
        $notificationPusher = $serviceManager->get(NotificationPusherFactory::class);
        return new Notifier($entityLoader, $entityIndex, $notificationPusher);
    }
}
