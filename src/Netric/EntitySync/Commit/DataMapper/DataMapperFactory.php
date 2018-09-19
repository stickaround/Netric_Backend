<?php
/**
 * Service factory for the Entity Sync Commit DataMapper
 *
 * @author Marl Tumulak <marl.tumulak@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\EntitySync\Commit\DataMapper;

use Netric\Db\Relational\RelationalDbFactory;
use Netric\ServiceManager;
use Netric\ServiceManager\AccountServiceManagerInterface;

/**
 * Create a Entity Sync Commit DataMapper service
 */
class DataMapperFactory implements ServiceManager\AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return DataMapperInterface
     */
    public function createService(AccountServiceManagerInterface $sl)
    {
        $database = $sl->get(RelationalDbFactory::class);
        return new DataMapperRdb($sl->getAccount(), $database);
    }
}
