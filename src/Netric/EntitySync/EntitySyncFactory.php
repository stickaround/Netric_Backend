<?php

declare(strict_types=1);

namespace Netric\EntitySync;

use Netric\ServiceManager;

/**
 * Create a Entity Sync service
 */
class EntitySyncFactory implements ServiceManager\AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceManager\AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return EntitySync
     */
    public function createService(ServiceManager\AccountServiceManagerInterface $sl)
    {
        $dm = $sl->get(DataMapperFactory::class);
        return new EntitySync($dm);
    }
}
