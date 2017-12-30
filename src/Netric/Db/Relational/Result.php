<?php
namespace Netric\Db\Relational;

/**
 * Results of an executed PDO statement
 */
class Result
{
    /**
     * Return the rows in an associative array from fetch or fetchAll
     */
    const FETCH_ASSOC = \PDO::FETCH_ASSOC;

    /**
     * Returns the rows as a 0-index row with a column index.
     */
    const FETCH_NUM = \PDO::FETCH_NUM;

    /**
     * Return an array of both associative names and column indices.
     */
    const FETCH_BOTH = \PDO::FETCH_BOTH;

    /**
     * PDOStatement we are wrapping
     *
     * @var \PDOStatement
     */
    private $oStatement = null;

    /**
     * Instantiate the Result
     *
     * @param \PDOStatement $oStatement A statement that has been executed
     */
    public function __construct(\PDOStatement $oStatement)
    {
        $this->oStatement = $oStatement;
    }

    /**
     * Fetch a single row from the PDO statement response
     *
     * E.g.
     *     $aResult = $oStatement->fetch();
     * Will result in an associative array
     * 
     * @return array
     */
    public function fetch()
    {
        /*
         * We have hard-coded the results type to FETCH_ASSOC due to 
         * some heated debate beween Donny, Carl, and Sky (me).
         * Since most use-cases will only need an associative array,
         * we will leave it hard coded for now but if anyone needs
         * a diffrent fetch type (link FETCH_NUM) then we can add it
         * as a param.
         * - Sky stebnicki, 2015-11-19
         */
        return $this->oStatement->fetch(self::FETCH_ASSOC);
    }

    /**
     * Fetch all rows from the PDO statement response
     *
     * E.g.
     *     $aResults = $oStatement->fetchAll();
     * Will result in an array of associative arrays
     *
     * @return array of arrays
     */
    public function fetchAll()
    {
        /*
         * We have hard-coded the results type to FETCH_ASSOC due to 
         * some heated debate beween Donny, Carl, and Sky (me).
         * Since most use-cases will only need an associative array,
         * we will leave it hard coded for now but if anyone needs
         * a diffrent fetch type (link FETCH_NUM) then we can add it
         * as a param.
         * - Sky stebnicki, 2015-11-19
         */
        return $this->oStatement->fetchAll(self::FETCH_ASSOC);
    }

    /**
     * Fetch the row count from the statement response
     *
     * @return int
     */
    public function rowCount()
    {
        return $this->oStatement->rowCount();
    }
}
