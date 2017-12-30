<?php
namespace Netric\Db\Relational;

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
     * Prepares a SQL statement
     *
     * @param string $sSql The SQL query to prepare
     * @param array $aParams
     *
     * @return Statement
     */
    public function prepare($sSql, array $aParams = []);

    /**
     * Prepares and executes a statement returning a Results object
     *
     * E.g. 
     * $oRDbConnection->query(
     *      "SELECT amount FROM thrust WHERE thrustid = :thrustid",
     *      [ 'thrustid' => 1 ]
     * )->fetchAll();
     *
     * @param string $sSql
     * @param array $aParams
     * @param array $aTableNames
     * @return Result Result set
     */
    public function query($sSql, array $aParams = [], array $aTableNames = []);

    /**
     * Starts a DB Transaction.
     *
     * @return bool
     */
    public function startTransaction();

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
     * Get the last insert id
     *
     * @param string $sName explicitly get last insert for sequence name
     * @return int
     */
    public function lastInsertId($sName = null);
}
