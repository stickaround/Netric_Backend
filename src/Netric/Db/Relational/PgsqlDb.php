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
    public function createNamespace(string $namespace) : bool
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
    public function deleteNamespace(string $namespace) : bool
    {
        $this->query('DROP SCHEMA ' . $namespace);
        return true;
    }

    /**
     * Find out if a column for a specific table exists
     *
     * @param string $tableName
     * @param string $columnName
     * @return bool true if the column already exists, false if it does not
     */
    public function columnExists(string $tableName, string $columnName) : bool
    {
        // Check if we explictely passed the schema in dot notation schema.table
        if (strpos($table, '.')) {
            $parts = explode(".", $tableName);
            $schema = $parts[0];
            $tableName = $parts[1];
        } else {
            $schema = $this->schemaName;
        }

        $sql = 'SELECT column_name FROM information_schema.columns ' .
            'WHERE table_name=:table AND column_name=:column';
        $whereParams = ['table' => $tableName, 'column' => $columnName];
        if ($schema) {
            $sql .= ' AND table_schema=:schema';
            $whereParams['schema'] = $schema;
        }

        $result = $this->query($sql, $whereParams);
        return ($result->rowCount() > 0);
    }

    /**
     * Find out if a table exists in the database
     *
     * @param string $tableName
     * @return bool true if the table exists, false if it does not
     */
    public function tableExists(string $tableName) : bool
    {
        $sql = 'SELECT tablename FROM pg_tables WHERE tablename=:table_name ';
        $whereParams = ['table_name' => $tableName];
        if ($this->schemaName) {
            $sql .= ' AND schemaname=:schema';
            $whereParams['schema'] = $this->schemaName;
        }

        $result = $this->query($sql, $whereParams);
        return ($result->rowCount() > 0);
    }

    /**
     * Find out if a namespace exists
     *
     * @param string $namespace
     * @return bool
     */
    public function namespaceExists(string $namespace) : bool
    {
        $sql = 'SELECT nspname from pg_namespace where nspname=:namesp';
        $result = $this->query($sql, ['namesp' => $namespace]);
        return ($result->rowCount() > 0);
    }
}