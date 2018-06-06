<?php
namespace Netric\Db\Relational;

/**
 * Database for SQLite
 */
class SqliteDb extends AbstractRelationalDb
{
    /**
     * Get the file name
     *
     * @param string
     * @return string
     */
    protected function getDataSourceString()
    {
        return "sqlite:" . $this->getHostOrFileName();
    }
}
