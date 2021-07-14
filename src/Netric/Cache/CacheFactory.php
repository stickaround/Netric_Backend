<?php

namespace Netric\Cache;

use Netric\Config\ConfigFactory;
use Netric\ServiceManager\ServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;

/**
 * Create a Cache service
 */
class CacheFactory implements ServiceFactoryInterface
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
        if ($config->cache->driver === 'redis') {
            return new RedisCache($config->cache);
        }

        return new MemcachedCache($config->cache);
    }
}
