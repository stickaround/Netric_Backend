<?php

/**
 * Service factory for the Entity Groupings Loader
 *
 * @author Marl Tumulak <marl.tumulak@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\EntityGroupings;

use Netric\Cache\CacheFactory;
use Netric\EntityGroupings\DataMapper\EntityGroupingDataMapperFactory;
use Netric\ServiceManager;

/**
 * Create a Entity Loader service
 */
class LoaderFactory implements ServiceManager\AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceManager\AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return Loader
     */
    public function createService(ServiceManager\AccountServiceManagerInterface $sl)
    {
        $dm = $sl->get(EntityGroupingDataMapperFactory::class);
        $cache = $sl->get(CacheFactory::class);

        return new Loader($dm, $cache);
    }
}
