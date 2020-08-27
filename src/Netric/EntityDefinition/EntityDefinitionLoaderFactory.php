<?php

/**
 * Service factory for the Entity Definition Loader
 *
 * @author Marl Tumulak <marl.tumulak@aereus.com>
 * @copyright 2016 Aereus
 */

namespace Netric\EntityDefinition;

use Netric\Cache\CacheFactory;
use Netric\Config\ConfigFactory;
use Netric\EntityDefinition\DataMapper\EntityDefinitionDataMapperFactory;
use Netric\ServiceManager\AccountServiceFactoryInterface;
use Netric\ServiceManager\AccountServiceManagerInterface;

/**
 * Create a Entity Definition Loader service
 */
class EntityDefinitionLoaderFactory implements AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param AccountServiceManagerInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return EntityDefinitionLoader
     */
    public function createService(AccountServiceManagerInterface $serviceLocator)
    {
        $definitionDataMapper = $serviceLocator->get(EntityDefinitionDataMapperFactory::class);
        $configLoader = $serviceLocator->get(ConfigFactory::class);
        $cache = $serviceLocator->get(CacheFactory::class);

        return new EntityDefinitionLoader($definitionDataMapper, $configLoader, $cache);
    }
}
