<?php

declare(strict_types=1);

namespace Netric\EntitySync;

use Netric\Db\Relational\RelationalDbFactory;
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
        $database = $sl->get(RelationalDbFactory::class);
        return new DataMapperRdb($sl->getAccount(), $database);
    }
}
