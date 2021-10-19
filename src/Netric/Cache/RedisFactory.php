<?php

declare(strict_types=1);

namespace Netric\Cache;

use Netric\Config\ConfigFactory;
use Netric\ServiceManager\ServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;

/**
 * Create a Cache service
 */
class RedisFactory implements ServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return CacheInterface
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get(ConfigFactory::class);
        return new RedisCache($config->cache);
    }
}
