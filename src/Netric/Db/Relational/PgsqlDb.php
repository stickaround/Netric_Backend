<?php
namespace Netric\Db\Relational;

/**
 * Database for PGSQL
 */
class PgsqlDb extends AbstractRelationalDb implements RelationalDbInterface
{
    /**
     * PGSQL schema may be set for segregating account data
     *
     * @var string
     */
    private $schemaName = 'public';

    /**
     * Get data source connection string
     *
     * @return string
     */
    protected function getDataSourceName()
    {
        return "pgsql:dbname=" . $this->getDatabaseName() .
            ";host=" . $this->getHostOrFileName();
    }

    /**
     * Set a namespace for all database transactions
     * 
     * This will not be implemented in the AbstractRelationalDb class because
     * the concept of namespaces is so unique to each database system.
     * 
     * For exmaple, in postgresql a namespace is called a schema. In mysql
     * databases are essentially schemas.
     *
     * @param string $namespace
     * @param bool $createIfMissing If true then create the namespace if it could not be set
     * @return void
     * @throws DatabaseQueryException on failure to create if missing
     */
    public function setNamespace(string $namespace, bool $createIfMissing = false)
    {
        $this->schemaName = $namespace;
        $this->query('SET search_path TO ' . $namespace);
    }

    /**
     * Create a unique namespace for segregating user data
     *
     * @param string $namespace
     * @return bool true on success
     * @throws DatabaseQueryException on failure
     */
    public function createNamespace(string $namespace)
    {
        $this->query('CREATE SCHEMA ' . $namespace);
        return true;
    }

    /**
     * Delete a unique namespace
     *
     * @param string $namespace
     * @return bool true on success
     * @throws DatabaseQueryException on failure
     */
    public function deleteNamespace(string $namespace)
    {
        $this->query('DROP SCHEMA ' . $namespace);
        return true;
    }
}