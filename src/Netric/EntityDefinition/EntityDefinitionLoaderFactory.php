<?php

/**
 * Service factory for the Entity Definition Loader
 *
 * @author Marl Tumulak <marl.tumulak@aereus.com>
 * @copyright 2016 Aereus
 */

namespace Netric\EntityDefinition;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Cache\CacheFactory;
use Netric\Config\ConfigFactory;
use Netric\EntityDefinition\DataMapper\EntityDefinitionDataMapperFactory;

/**
 * Create a Entity Definition Loader service
 */
class EntityDefinitionLoaderFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return EntityDefinitionLoader
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        $definitionDataMapper = $serviceLocator->get(EntityDefinitionDataMapperFactory::class);
        $configLoader = $serviceLocator->get(ConfigFactory::class);
        $cache = $serviceLocator->get(CacheFactory::class);

        return new EntityDefinitionLoader($definitionDataMapper, $configLoader, $cache);
    }
}
