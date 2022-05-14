<?php

namespace NetricTest\Application;

use Netric\Application\DataMapperInterface;
use Netric\Application\ApplicationRdbDataMapper;
use Netric\Db\Relational\PgsqlDb;

class DataMapperApplicationRdbTest extends AbstractDataMapperTests
{
    /**
     * Get an implementation specific DataMapper
     *
     * @param string $optDbName Optional different name to use for the database
     * @return DataMapperInterface
     */
    protected function getDataMapper($optDbName = null)
    {
        $dbName = ($optDbName) ? $optDbName : $this->config->db['dbname'];

        return new ApplicationRdbDataMapper(
            $this->config->db['syshost'],
            $dbName,
            $this->config->db['user'],
            $this->config->db['password']
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
        $db = new PgsqlDb(
            $this->config->db['syshost'],
            $this->config->db['dbname'],
            $this->config->db['user'],
            $this->config->db['password']
        );

        $db->query("ALTER DATABASE $dbName CONNECTION LIMIT 0;");

        $sql = "SELECT pg_terminate_backend(pid) FROM pg_stat_activity WHERE datname=:database_name";
        $db->query($sql, ["database_name" => $dbName]);

        $db->query("DROP DATABASE $dbName");
    }
}
