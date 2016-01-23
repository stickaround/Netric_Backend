<?php
namespace NetricTest\Application;

use Netric\Application\DataMapperInterface;
use Netric\Application\DataMapperPgsql;
use Netric\Config;
use Netric\Db\Pgsql;
use PHPUnit_Framework_TestCase;

class DataMapperPgsqlTest extends AbstractDataMapperTests
{
    /**
     * Get an implementation specific DataMapper
     *
     * @param string $optDbName Optional different name to use for the database
     * @return DataMapperInterface
     */
    protected function getDataMapper($optDbName = null)
    {
        $config = new Config();
        $dbName = ($optDbName) ? $optDbName : $config->db['sysdb'];

        return new DataMapperPgsql(
            $config->db['syshost'],
            $dbName,
            $config->db['user'],
            $config->db['password']
        );
    }

    /**
     * This is a cleanup method that we need done mantually in the datamapper driver
     *
     * We do not want to expose this in the application datamapper since the
     * application database should NEVER be deleted. So we leave it up to each
     * drive to manually delete or drop a temp/test database.
     *
     * @param string $dbName The name of the database to drop
     */
    protected function deleteDatabase($dbName)
    {
        $config = new Config();

        $db = new Pgsql(
            $config->db['syshost'],
            $config->db['sysdb'],
            $config->db['user'],
            $config->db['password']
        );

        $db->query("DROP DATABASE $dbName");
    }
}