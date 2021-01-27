<?php

declare(strict_types=1);

namespace Netric\EntitySync\Commit\DataMapper;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Db\Relational\RelationalDbFactory;

/**
 * Create a Entity Sync Commit DataMapper service
 */
class DataMapperFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return DataMapperInterface
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $database = $serviceLocator->get(RelationalDbFactory::class);        
        return new DataMapperRdb($database);
    }
}
