<?php

namespace Netric\Application;

use Netric\Db\Relational\RelationalDbFactory;
use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;

/**
 * Create database setup service
 */
class DatabaseSetupFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $sl ServiceLocator for injecting dependencies
     * @return DatabaseSetup
     */
    public function createService(ServiceLocatorInterface $sl)
    {
        $database = $sl->get(RelationalDbFactory::class);
        $appDataMapper = $sl->get(DataMapperFactory::class);
        $schemaDefinition = include(__DIR__ . "/../../../data/db/schema.php");
        return new DatabaseSetup($database, $schemaDefinition, $appDataMapper);
    }
}
