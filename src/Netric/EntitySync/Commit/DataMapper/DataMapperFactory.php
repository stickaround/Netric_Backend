<?php

declare(strict_types=1);

namespace Netric\EntitySync\Commit\DataMapper;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Db\Relational\RelationalDbFactory;

/**
 * Create a Entity Sync Commit DataMapper service
 */
class DataMapperFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return DataMapperInterface
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        $database = $serviceLocator->get(RelationalDbFactory::class);
        return new DataMapperRdb($database);
    }
}
