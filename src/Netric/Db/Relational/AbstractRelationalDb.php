<?php
namespace Netric\Db\Relational;

use Netric\Db\Relational\Exception\DatabaseException;
use Netric\Db\Relational\Exception\DatabaseQueryException;

/**
 * Base database class that wraps a PDO connection to the database
 */
abstract class AbstractRelationalDb
{
    /**
     * Default connection timeout, in seconds
     */
    const CONNECT_TIMEOUT = 2;

    /**
     * Number of times to attempt a connection
     */
    const MAX_CONNECT_ATTEMPTS = 2;

    /**
     * @var \PDO $oConnection PDO Connection
     */
    private $pdoConnection = null;

    /**
     * @var string $databaseUser
     */
    private $databaseUser;

    /**
     * @var string $databasePassword
     */
    private $databasePassword;

    /**
     * @var integer $timeoutInSeconds Connection timeout in seconds
     */
    private $timeoutInSeconds;

    /**
     * @var string $databaseName Used to build our timing key
     */
    private $databaseName;

    /**
     * Host or file where databases are found
     *
     * @var string
     */
    private $hostOrFileName;

    /**
     * @var array $connectionAttributes the PDO connection attributes to use with the connection
     */
    private $connectionAttributes;

    /**
     * Validate and store the RDb parameters
     *
     * @param string $hostOrFileName Either a hostname or file path based on driver
     * @param string $databaseName
     * @param string $databaseUser
     * @param string $databasePassword
     * @param integer $timeoutInSeconds
     */
    public function __construct(
        $hostOrFileName,
        $databaseName = "",
        $databaseUser = "",
        $databasePassword = "",
        $timeoutInSeconds = self::CONNECT_TIMEOUT
    ) {
        $this->hostOrFileName = $hostOrFileName;
        $this->databaseUser = $databaseUser;
        $this->databasePassword = $databasePassword;
        $this->timeoutInSeconds = $timeoutInSeconds;
        $this->databaseName = $databaseName;

        // Set all errors to be exceptions
        $this->connectionAttributes = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_PERSISTENT => true,
        ];

        // If we haven't set an explicit timeout in the connection attributes, use the timeout provided in the constructor
        if (!isset($this->connectionAttributes[\PDO::ATTR_TIMEOUT])) {
            $this->connectionAttributes[\PDO::ATTR_TIMEOUT] = $timeoutInSeconds;
        }
    }

    /**
     * Close the connetion to the database
     */
    public function __destruct()
    {
        $this->closeConnection();
    }

    /**
     * Required function for all derived classes to rovide their PDO connections string
     *
     * @return string
     */
    abstract protected function getDataSourceName();

    /**
     * Run any commands/use statements to switch to a unique namespace
     */
    abstract protected function useSetNamespace();

    /**
     * Chose the current connection if it exists
     *
     * @return void
     */
    protected function closeConnection()
    {
        if (!is_null($this->pdoConnection)) {
            $this->pdoConnection = null;
        }
    }

    /**
     * Get the current configured host or file name
     *
     * @return string
     */
    protected function getHostOrFileName()
    {
        return $this->hostOrFileName;
    }

    /**
     * Get the current configured database name
     *
     * @return string
     */
    protected function getDatabaseName()
    {
        return $this->databaseName;
    }

    /**
     * Lazy-load the PDO database connection since we don't want to connect on every new
     *
     * @return \PDO $this->pdoConnection
     * @throws DatabaseException
     */
    private function getConnection()
    {
        if (!is_null($this->pdoConnection)) {
            return $this->pdoConnection;
        }

        $oLastException = null;
        for ($numAttempts = 1; $numAttempts <= self::MAX_CONNECT_ATTEMPTS; $numAttempts++) {
            try {
                $this->pdoConnection = new \PDO(
                    $this->getDataSourceName(),
                    $this->databaseUser,
                    $this->databasePassword,
                    $this->connectionAttributes
                );

                // If the account is using a special namespace, then make sure the
                // specific database impelmentaiton uses it
                $this->useSetNamespace();

                return $this->pdoConnection;
            } catch (\Exception $oException) {
                $oLastException = $oException;
            }

            if (!empty($this->pdoConnection)) {
                return $this->pdoConnection;
            }
        }

        // Bummer! No connection could be established
        throw new Exception\DatabaseConnectionException(
            'Could not establish connection after ' .
                self::MAX_CONNECT_ATTEMPTS . ' attempts. Exception: ' .
                $oLastException->getMessage()
        );
    }

    /**
     * Prepares a SQL statement
     *
     * @param string $sqlQuery
     * @param array $params
     *
     * @return Statement
     */
    private function prepareStatement($sqlQuery, array $params = [])
    {
        $pdoConnection = $this->getConnection();
        $pdoStatement = $pdoConnection->prepare($sqlQuery);
        return new Statement($pdoStatement, $params);
    }

    /**
     * Prepares and executes a statement returning a Results object
     *
     * Example:
     * $database->query(
     *      "SELECT id FROM users WHERE nane = :name",
     *      [ 'name' => 1 ]
     * )->fetchAll();
     *
     * @param string $sqlQuery
     * @param array $params
     * @return Result
     */
    public function query($sqlQuery, array $params = [])
    {
        // Prepare a statement for the main query using params
        $statement = $this->prepareStatement($sqlQuery, $params);

        // Start timing in case we want to log slow queries
        //$startTime = microtime(true);

        try {
            $result = $statement->execute();

            // TODO: Possibly log timing here
            // $queryTimeInMs = (int)((microtime(true) - $startTime) * 1000);

            return $result;
        } catch (\PDOException $oPdoException) {
            /*
             * $statement->execute will throw a PDOException if a query fails.
             * We will wrap the details of this into a DatabaseQueryException
             * and allow the client to handle the failure without having to be
             * aware of PDOException.
             */
            throw new DatabaseQueryException(
                $oPdoException->getMessage() . ", query=" . $sqlQuery
            );
        }
    }

    /**
     * Insert a row into a table
     *
     * @param string $tableName
     * @param array $params Associative array where key = columnName
     * @throws DatabaseQueryException from $this->query if the query fails
     * @return int ID created for the primary key (if exists) otherwize 0
     */
    public function insert(string $tableName, array $params)
    {
        $this->beginTransaction();
        
        // Assume the insert does not have an ID to return
        $insertedId = 0;

        // Get all columns param keys and add to insert statement
        $columns = array_keys($params);
        $sql = 'INSERT INTO ' . $tableName . '(' . implode(',', $columns) . ')';
        // Add values as params by prefixing each with ':'
        $sql .= ' VALUES(:' . implode(',:', $columns) . ')';

        // Run query, get next value (if selected), and commit
        $this->query($sql, $params);

        // Wrap get last id in try catch since we do not know if the table has a serial id
        try {
            $insertedId = $this->getLastInsertId();
        } catch (DatabaseException $ex) {
            // Do nothing because we expect this to happen in some cases
        }

        $this->commitTransaction();

        return $insertedId;
    }

    /**
     * Update a table row by matching conditional params
     *
     * @param string $tableName
     * @param array $params
     * @param array $whereParams
     * @return int Number of rows updated
     */
    public function update(string $tableName, array $params, array $whereParams)
    {
        $sql = 'UPDATE ' . $tableName . ' SET ';
        
        // Add update statements
        $updateStatements = [];
        foreach ($params as $colName => $colValue) {
            $updateStatements[] = $colName . '=:' . $colName;
        }
        $sql .= implode(',', $updateStatements);

        // Add where conditions to limit the update
        $whereStatements = [];
        $escapedWhereParams = [];
        foreach ($whereParams as $colName => $colCondValue) {
            $whereStatements[] = $colName . '=:cond_' . $colName;
            $escapedWhereParams['cond_' . $colName] = $colCondValue;
        }

        if (count($whereStatements) > 0) {
            $sql .= ' WHERE ' . implode(' AND ', $whereStatements);
        }

        // Run the update and return the id as the result
        $result = $this->query($sql, array_merge($params, $escapedWhereParams));

        // Let the user know how many rows were updated
        return $result->rowCount();
    }

    /**
     * Delete a table row by simple matching conditional params
     *
     * @param string $tableName
     * @param array $whereParams
     * @return int Number of rows updated
     */
    public function delete(string $tableName, array $whereParams)
    {
        $sql = 'DELETE FROM ' . $tableName;

        // Add where conditions to limit the delete
        $whereStatements = [];
        foreach ($whereParams as $colName => $colCondValue) {
            $whereStatements[] = $colName . '=:' . $colName;
        }

        if (count($whereStatements) > 0) {
            $sql .= ' WHERE ' . implode(' AND ', $whereStatements);
        }

        // Run the update and return the id as the result
        $result = $this->query($sql, $whereParams);

        // Let the user know how many rows were updated
        return $result->rowCount();
    }

    /**
     * Starts a DB transaction.
     *
     * @return bool
     */
    public function beginTransaction()
    {
        return $this->getConnection()->beginTransaction();
    }

    /**
     * Commits the current DB transaction.
     *
     * @return bool
     */
    public function commitTransaction()
    {
        return $this->getConnection()->commit();
    }

    /**
     * Rolls back the current DB transaction.
     *
     * @return bool
     */
    public function rollbackTransaction()
    {
        return $this->getConnection()->rollBack();
    }

    /**
     * Get the last inserted id of a sequence
     *
     * @param string $sequenceName If null then primary key is used
     * @return int
     */
    public function getLastInsertId($sequenceName = null)
    {
        $pdoConnection = $this->getConnection();
        try {
            return $pdoConnection->lastInsertId($sequenceName);
        } catch (\PDOException $exception) {
            throw new DatabaseException(
                'Unable to get the last inserted ID. This often happens if this ' .
                    'was called after the transaction was committed, or the ' .
                    'table does not have a serialized primary key.'
            );
        }
    }

    /**
     * Closes the database connection
     */
    public function close()
    {
        // TODO: Might want to kill the connection with a query
        // 'KILL CONNECTION_ID()'

        // Close connection
        $this->pdoConnection = null;
    }
}
