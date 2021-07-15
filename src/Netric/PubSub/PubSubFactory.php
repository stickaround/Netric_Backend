<?php

declare(strict_types=1);

namespace Netric\PubSub;

use Netric\Cache\RedisFactory;
use Netric\ServiceManager\ServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;

/**
 * Create a Cache service
 */
class PubSubFactory implements ServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return CacheInterface
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $serviceLocator->get(RedisFactory::class);
    }
}
