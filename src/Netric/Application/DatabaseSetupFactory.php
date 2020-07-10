<?php

namespace Netric\Application;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Db\Relational\RelationalApplicationDbFactory;
use Netric\Application\DataMapperFactory;

/**
 * Create database setup service
 */
class DatabaseSetupFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return SchemaDataMapperInterface
     */
    public function createService(ServiceLocatorInterface $sl)
    {
        $database = $sl->get(RelationalApplicationDbFactory::class);
        $appDataMapper = $sl->get(DataMapperFactory::class);
        $schemaDefinition = include(__DIR__ . "/../../../data/db/schema.php");
        return new DatabaseSetup($database, $schemaDefinition, $appDataMapper);
    }
}
