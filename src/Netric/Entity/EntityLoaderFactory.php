<?php

namespace Netric\Entity;

use Netric\Entity\EntityFactoryFactory;
use Netric\Cache\CacheFactory;
use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Entity\DataMapper\EntityDataMapperFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;

/**
 * Create a Entity Loader service
 */
class EntityLoaderFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return EntityLoader
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityDataMapper = $serviceLocator->get(EntityDataMapperFactory::class);
        $definitionLoader = $serviceLocator->get(EntityDefinitionLoaderFactory::class);
        $entityFactory = $serviceLocator->get(EntityFactoryFactory::class);
        $cache = $serviceLocator->get(CacheFactory::class);

        return new EntityLoader($entityDataMapper, $definitionLoader, $entityFactory, $cache);
    }
}
