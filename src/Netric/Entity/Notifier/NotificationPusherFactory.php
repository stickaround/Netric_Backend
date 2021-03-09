<?php

namespace Netric\Entity\Notifier;

use Netric\Config\ConfigFactory;
use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use NotificationPusherSdk\NotificationPusherClient;
use NotificationPusherSdk\NotificationPusherClientInterface;

/**
 * Create a new notification pusher client
 */
class NotificationPusherFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceManager ServiceLocator for injecting dependencies
     * @return NotificationPusherClientInterface
     */
    public function createService(ServiceLocatorInterface $serviceManager)
    {
        // Construct the NotificationPusherClient which just makes calls the an external service
        $config = $serviceManager->get(ConfigFactory::class);
        return new NotificationPusherClient('netric', 'secret', 'server');
    }
}
