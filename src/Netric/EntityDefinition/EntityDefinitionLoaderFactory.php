<?php

/**
 * Service factory for the Entity Definition Loader
 *
 * @author Marl Tumulak <marl.tumulak@aereus.com>
 * @copyright 2016 Aereus
 */

namespace Netric\EntityDefinition;

use Netric\Cache\CacheFactory;
use Netric\ServiceManager;
use Netric\EntityDefinition\DataMapper\DataMapperFactory;

/**
 * Create a Entity Definition Loader service
 */
class EntityDefinitionLoaderFactory implements ServiceManager\AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceManager\AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return EntityDefinitionLoader
     */
    public function createService(ServiceManager\AccountServiceManagerInterface $sl)
    {
        $dm = $sl->get(DataMapperFactory::class);
        $cache = $sl->get(CacheFactory::class);

        return new EntityDefinitionLoader($dm, $cache, $sl->getAccount());
    }
}
