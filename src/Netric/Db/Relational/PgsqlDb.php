<?php

namespace Netric\Db\Relational;

use Netric\Db\Relational\Exception\DatabaseQueryException;

/**
 * Database for PostgreSQL
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
     * @param string $databaseName Optional name of the database to connect to
     * @return string
     */
    protected function getDataSourceName($databaseName = "")
    {
        $dataSourceName = "pgsql:";
        if ($databaseName) {
            $dataSourceName .= "dbname=" . $databaseName . ";";
        }
        return $dataSourceName . "host=" . $this->getHostOrFileName();
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
        // If the namespace has changed, reset the connection
        if ($namespace != $this->scemaName) {
            $this->closeConnection();
        }

        $this->schemaName = $namespace;
    }

    /**
     * Get the current set namespace if set
     */
    public function getNamespace(): string
    {
        return $this->schemaName;
    }

    /**
     * Run any commands/use statements to switch to a unique namespace
     */
    protected function useSetNamespace()
    {
        if ($this->schemaName) {
            $this->query('SET search_path TO ' . $this->schemaName);
        }
    }

    /**
     * Create a unique namespace for segregating user data
     *
     * @param string $namespace
     * @return bool true on success
     * @throws DatabaseQueryException on failure
     */
    public function createNamespace(string $namespace): bool
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
    public function deleteNamespace(string $namespace): bool
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
    public function columnExists(string $tableName, string $columnName): bool
    {
        // Check if we explicitly passed the schema in dot notation schema.table
        if (strpos($tableName, '.')) {
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
    public function tableExists(string $tableName): bool
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
    public function namespaceExists(string $namespace): bool
    {
        $sql = 'SELECT nspname from pg_namespace where nspname=:namesp';
        $result = $this->query($sql, ['namesp' => $namespace]);
        return ($result->rowCount() > 0);
    }

    /**
     * Check if an index exists by name
     *
     * @param string $idxname The name of the index to look for
     * @return bool true if the index was found, false if it was not
     */
    public function indexExists(string $idxname): bool
    {
        $sql = "SELECT * FROM pg_indexes WHERE indexname=:index_name";
        $params = ["index_name" => $idxname];

        if ($this->schemaName) {
            $sql .= " and schemaname=:schema_name";
            $params["schema_name"] = $this->schemaName;
        }

        $result = $this->query($sql, $params);

        return $result->rowCount() > 0;
    }

    /**
     * Function that will check if constraint is already existing in a table
     *
     * @param String $tableName The table to be checked
     * @param String $constraintName The name of the constraint that we are looking for
     * @return bool
     */
    public function constraintExists(string $tableName, string $constraintName): bool
    {
        $sql = "SELECT table_name FROM information_schema.table_constraints
					WHERE table_name=:table_name and constraint_name=:constraint_name;";

        $result = $this->query($sql, [
            "table_name" => $tableName,
            "constraint_name" => $constraintName
        ]);

        return $result->rowCount() > 0;
    }

    /**
     * Function that will get the primary key of the table
     *
     * @param String $tableName The name of the table where we will be getting its primary key
     * @return array
     */
    public function getPrimaryKeys(string $tableName): array
    {
        $sql = "SELECT a.attname, format_type(a.atttypid, a.atttypmod) AS data_type
				FROM   pg_index i
				JOIN   pg_attribute a ON a.attrelid = i.indrelid
									 AND a.attnum = ANY(i.indkey)
				WHERE  i.indrelid='{$tableName}'::regclass
				AND    i.indisprimary ORDER BY attname";

        $result = $this->query($sql);
        return $result->fetchAll();
    }

    /**
     * Function that will check if the columnName provided is a primary key
     *
     * @param String $tableName The name of the table that we will be checking if columnName is primary key
     * @param Array|String $columnName The name of the column that will be checked
     * @return bool
     */
    public function isColumnPrimaryKey(string $tableName, $columnName): bool
    {
        $primaryKeys = $this->getPrimaryKeys($tableName);

        $pKeyNames = [];
        foreach ($primaryKeys as $pKey) {
            $pKeyNames[] = $pKey["attname"];
        }

        $actualPkeyName = implode("_", $pKeyNames);

        // Check if the names are the same - have all the same columns
        $columnNames = (is_array($columnName)) ? $columnName : array($columnName);
        asort($columnNames);
        return ($actualPkeyName == implode("_", $columnNames));
    }

    /**
     * Get sequence name to pass to lastInsertid
     *
     * PostgreSQL uses tablename_columnname_seq for every sequence name
     *
     * @param string $tableName
     * @param string $columnName
     * @return string | null
     */
    protected function getSequenceName(string $tableName, string $columnName): ?string
    {
        // Default to tablename_columname_seq
        return $tableName . '_' . $columnName . '_seq';
    }
}
