<?php

namespace Netric\Entity\Notifier;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Entity\EntityLoaderFactory;
use Netric\Entity\Notifier\Sender\PublicUserEmailSenderFactory;
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
        $entityLoader = $serviceManager->get(EntityLoaderFactory::class);
        $entityIndex = $serviceManager->get(IndexFactory::class);
        $notificationPusher = $serviceManager->get(NotificationPusherFactory::class);
        $publicEmailSender = $serviceManager->get(PublicUserEmailSenderFactory::class);
        return new Notifier($entityLoader, $entityIndex, $notificationPusher, $publicEmailSender);
    }
}
