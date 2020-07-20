<?php

declare(strict_types=1);

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
     * @param AccountServiceManagerInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return DataMapperInterface
     */
    public function createService(AccountServiceManagerInterface $serviceLocator)
    {
        $database = $serviceLocator->get(RelationalDbFactory::class);
        return new DataMapperRdb($serviceLocator->getAccount(), $database);
    }
}
