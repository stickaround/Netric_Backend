<?php

namespace Netric\Db\Relational;

use Netric\Db\Relational\Exception\DatabaseQueryException;

/**
 * In most cases we try to keep relational db usage as generic as possible so
 * that a dependent class could use any of the supported drivers to store
 * and retrieve state. In cases where this is not possible we require that
 * a DataMapper created for each specific driver and be tested appropriately
 * via integration tests to assure all drivers work as designed.
 */
interface RelationalDbInterface
{
    /**
     * Prepares and executes a statement returning a Results object
     *
     * Example:
     * $oRDbConnection->query(
     *      "SELECT id FROM users WHERE nane = :name",
     *      [ 'name' => 1 ]
     * )->fetchAll();
     *
     * @param string $sqlQuery
     * @param array $params
     * @return Result Result set
     */
    public function query($sqlQuery, array $params = []);

    /**
     * Insert a row into a table
     *
     * @param string $tableName
     * @param array $params Associative array where key = columnName
     * @param string $sequenceForNextId Set which column is the primary key to get the next id from
     * @throws DatabaseQueryException from $this->query if the query fails
     * @return int ID created for the primary key (if exists) otherwize 0
     */
    public function insert(string $tableName, array $params, string $sequenceForNextId = "");

    /**
     * Update a table row by matching conditional params
     *
     * @param string $tableName
     * @param array $params
     * @param array $whereParams
     * @return int Number of rows updated
     */
    public function update(string $tableName, array $params, array $whereParams);

    /**
     * Delete a table row by matching conditional params
     *
     * @param string $tableName
     * @param array $whereParams
     * @return int Number of rows updated
     */
    public function delete(string $tableName, array $whereParams);

    /**
     * Check if a connection is active
     *
     * @return bool
     */
    public function checkConnection(): bool;

    /**
     * Starts a DB Transaction.
     *
     * @return bool
     */
    public function beginTransaction();

    /**
     * Commits the current DB transaction.
     *
     * @return bool
     */
    public function commitTransaction();

    /**
     * Rolls back the current DB transaction.
     *
     * @return bool
     */
    public function rollbackTransaction();

    /**
     * Get the last inserted id of a sequence
     *
     * @param string $sequenceName If null then primary key is used
     * @return int
     */
    public function getLastInsertId($sequenceName = null);

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
    public function setNamespace(string $namespace, bool $createIfMissing = false);

    /**
     * Get the current namespace
     *
     * @return string The name of the current namespace (if set or empty string)
     */
    public function getNamespace(): string;

    /**
     * Create a unique namespace for segregating user data
     *
     * @param string $namespace
     * @return bool true on success
     * @throws DatabaseQueryException on failure
     */
    public function createNamespace(string $namespace): bool;

    /**
     * Delete a unique namespace
     *
     * @param string $namespace
     * @return bool true on success
     * @throws DatabaseQueryException on failure
     */
    public function deleteNamespace(string $namespace): bool;

    /**
     * Find out if a column for a specific table exists
     *
     * @param string $tableName
     * @param string $columnName
     * @return bool true if the column already exists, false if it does not
     */
    public function columnExists(string $tableName, string $columnName): bool;

    /**
     * Find out if a table exists in the database
     *
     * @param string $tableName
     * @return bool true if the table exists, false if it does not
     */
    public function tableExists(string $tableName): bool;

    /**
     * Find out if a namespace exists
     *
     * @param string $namespace
     * @return bool
     */
    public function namespaceExists(string $namespace): bool;

    /**
     * Function that will check if constraint is already existing in a table
     *
     * @param String $tableName The table to be checked
     * @param String $constraintName The name of the constraint that we are looking for
     * @return bool
     */
    public function constraintExists(string $tableName, string $constraintName): bool;

    /**
     * Check if an index exists by name
     *
     * @param string $idxname The name of the index to look for
     * @return bool true if the index was found, false if it was not
     */
    public function indexExists(string $idxname): bool;

    /**
     * Function that will get the primary key of the table
     *
     * @param String $tableName The name of the table where we will be getting its primary key
     * @return array
     */
    public function getPrimaryKeys(string $tableName): array;

    /**
     * Function that will check if the columnName provided is a primary key
     *
     * @param String $tableName The name of the table that we will be checking if columnName is primary key
     * @param Array|String $columnName The name of the column that will be checked
     * @return bool
     */
    public function isColumnPrimaryKey(string $tableName, $columnName): bool;

    /**
     * Quote a param and escape it if needed
     *
     * @param mixed $param The parameter to qute
     * @return string Quoted string
     */
    public function quote($param): string;
}
