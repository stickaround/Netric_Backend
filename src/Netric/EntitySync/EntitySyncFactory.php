<?php
/**
 * Service factory for the Entity Sync
 *
 * @author Marl Tumulak <marl.tumulak@aereus.com>
 * @copyright 2016 Aereus
 */
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
