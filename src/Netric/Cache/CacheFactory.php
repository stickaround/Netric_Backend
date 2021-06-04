<?php

namespace Netric\Cache;

use Netric\Config\ConfigFactory;
use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;

/**
 * Create a Cache service
 */
class CacheFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return CacheInterface
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        $config = $serviceLocator->get(ConfigFactory::class);
        return new MemcachedCache($config->cache);
    }
}
