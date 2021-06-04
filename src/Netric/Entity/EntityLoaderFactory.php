<?php

namespace Netric\Entity;

use Netric\Entity\EntityFactoryFactory;
use Netric\Cache\CacheFactory;
use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Entity\DataMapper\EntityDataMapperFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;

/**
 * Create a Entity Loader service
 */
class EntityLoaderFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return EntityLoader
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        $entityDataMapper = $serviceLocator->get(EntityDataMapperFactory::class);
        $definitionLoader = $serviceLocator->get(EntityDefinitionLoaderFactory::class);
        $entityFactory = $serviceLocator->get(EntityFactoryFactory::class);
        $cache = $serviceLocator->get(CacheFactory::class);

        return new EntityLoader($entityDataMapper, $definitionLoader, $entityFactory, $cache);
    }
}
