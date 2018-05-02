<?php
/**
 * Service factory for the Entity Sync DataMapper
 *
 * @author Marl Tumulak <marl.tumulak@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\EntitySync;

use Netric\Db\DbFactory;
use Netric\ServiceManager;

/**
 * Create a Entity Sync Commit DataMapper service
 */
class DataMapperFactory implements ServiceManager\AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceManager\AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return DataMapperInterface
     */
    public function createService(ServiceManager\AccountServiceManagerInterface $sl)
    {
        $db = $sl->get(DbFactory::class);
        return new DataMapperPgsql($sl->getAccount(), $db);
    }
}
