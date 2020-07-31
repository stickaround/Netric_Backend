<?php

namespace Netric\Entity;

use Netric\Entity\EntityFactoryFactory;
use Netric\Cache\CacheFactory;
use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\ServiceManager\AccountServiceFactoryInterface;
use Netric\Entity\DataMapper\EntityDataMapperFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;

/**
 * Create a Entity Loader service
 */
class EntityLoaderFactory implements AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param AccountServiceManagerInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return EntityLoader
     */
    public function createService(AccountServiceManagerInterface $serviceLocator)
    {
        $entityDataMapper = $serviceLocator->get(EntityDataMapperFactory::class);
        $definitionLoader = $serviceLocator->get(EntityDefinitionLoaderFactory::class);
        $entityFactory = $serviceLocator->get(EntityFactoryFactory::class);
        $cache = $serviceLocator->get(CacheFactory::class);

        return new EntityLoader($entityDataMapper, $definitionLoader, $entityFactory, $cache);
    }
}
