<?php

namespace Netric\Entity\Notifier;

use Netric\Config\ConfigFactory;
use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use NotificationPusherSdk\NotificationPusherClient;
use NotificationPusherSdk\NotificationPusherClientInterface;

/**
 * Create a new notification pusher client
 */
class NotificationPusherFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return NotificationPusherClientInterface
     */
    public function __invoke(ServiceContainerInterface $serviceManager)
    {
        // Construct the NotificationPusherClient which just makes calls the an external service
        $config = $serviceManager->get(ConfigFactory::class);
        return new NotificationPusherClient(
            $config->notifications->push->account,
            $config->notifications->push->secret,
            $config->notifications->push->server
        );
    }
}
