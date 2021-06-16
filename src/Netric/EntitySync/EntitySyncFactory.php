<?php

declare(strict_types=1);

namespace Netric\EntitySync;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;

/**
 * Create a Entity Sync service
 */
class EntitySyncFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return EntitySync
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        $dm = $serviceLocator->get(DataMapperFactory::class);
        return new EntitySync($dm);
    }
}
