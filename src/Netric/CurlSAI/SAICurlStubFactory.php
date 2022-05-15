<?php

namespace Netric\CurlSAI;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Cache\CacheFactory;
use Netric\Application\DataMapperFactory as ApplicationDataMapperFactory;

/**
 * Create a new Application DataMapper service
 */
class SAICurlStubFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     *
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $cache = $serviceLocator->get(CacheFactory::class);
        $appDataMapper = $serviceLocator->get(ApplicationDataMapperFactory::class);
        return new SAI_CurlStub(
            $appDataMapper,
            $cache,
            $serviceLocator->getApplication()
        );
    }
}
