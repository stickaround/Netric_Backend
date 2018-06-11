<?php
namespace Netric\Db\Relational;

/**
 * Database for MySQL
 */
class MysqlDb extends AbstractRelationalDb
{
    /**
     * Get data source connection string
     *
     * @return string
     */
    protected function getDataSourceName()
    {
        return "mysql:dbname=" . $this->getDatabaseName() .
            ";host=" . $this->getHostOrFileName();
    }

    // TODO: fill in the rest of the functions required (see PgsqlDB)
}
