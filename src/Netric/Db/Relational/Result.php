<?php
namespace Netric\Db\Relational;

/**
 * Results of an executed PDO statement
 */
class Result
{
    /**
     * PDOStatement we are wrapping that contains executed results
     *
     * @var \PDOStatement
     */
    private $pdoStatement = null;

    /**
     * Instantiate the Result
     *
     * @param \PDOStatement $pdoStatement A PDO statement that has been executed
     */
    public function __construct(\PDOStatement $pdoStatement)
    {
        $this->pdoStatement = $pdoStatement;
    }

    /**
     * Cleanup the results
     */
    public function __destruct()
    {
        $this->pdoStatement = null;
    }

    /**
     * Fetch a single row from the PDO statement response
     * 
     * @return array Associative array of the next row
     */
    public function fetch()
    {
        return $this->pdoStatement->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Fetch all rows from the PDO statement response
     *
     * @return array Array of associative arrays for each row
     */
    public function fetchAll()
    {
        return $this->pdoStatement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get number of rows in the current result
     *
     * @return int
     */
    public function rowCount()
    {
        return $this->pdoStatement->rowCount();
    }
}
