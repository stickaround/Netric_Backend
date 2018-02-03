<?php

/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright Copyright (c) 2016 Aereus Corporation (http://www.aereus.com)
 */
namespace Netric\Application\Schema;

use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\ServiceManager\AccountServiceFactoryInterface;
use Netric\Db\DbFactory;

/**
 * Create the default DataMapper for account schemas
 */
class SchemaDataMapperFactory implements AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return SchemaDataMapperInterface
     */
    public function createService(AccountServiceManagerInterface $sl)
    {
        $dbh = $sl->get(DbFactory::class);
        $schemaDefinition = include(__DIR__ . "/../../../../data/schema/account.php");
        return new SchemaDataMapperPgsql($dbh, $schemaDefinition);
    }
}
