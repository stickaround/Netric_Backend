<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright Copyright (c) 2016 Aereus Corporation (http://www.aereus.com)
 */
namespace Netric\Account\Schema;

use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\ServiceManager\ServiceFactoryInterface;

/**
 * Create the default DataMapper for account schemas
 */
class SchemaDataMapperFactory implements ServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $sl ServiceLocator for injecting dependencies
     * @return SchemaDataMapperInterface
     */
    public function createService(ServiceLocatorInterface $sl)
    {
        $dbh = $sl->get("Db");
        $schemaDefinition = include(__DIR__ . "/../../../../data/schema/account.php");
        return new SchemaDataMapperPgsql($dbh, $schemaDefinition);
    }
}
