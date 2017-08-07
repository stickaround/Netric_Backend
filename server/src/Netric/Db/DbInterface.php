<?php

/*
 * Short description for file
 * 
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * 
 *  @author Sky Stebnicki <sky.stebnicki@aereus.com>
 *  @copyright 2014 Aereus
 */
namespace Netric\Db;

/**
 * Description of DbInterface
 *
 * @author Sky Stebnicki
 */
interface DbInterface 
{
    /**
     * Escape a string
     *
     * @param string $value
     * @return string Escaped string
     */
    public function escape($value);

    /**
     * Escape a number
     *
     * @param int $number
     * @return string Escaped string
     */
    public function escapeNumber($number);

    /**
     * Escape a date string
     *
     * @param string $date
     * @return string Escaped string
     */
    public function escapeDate($date);

    /**
     * Return number of rows for a given result
     *
     * @return int
     */
    public function getNumRows($result);

    /**
     * Execute an SQL query
     *
     * @param string $sql The sql to run
     * @return resource
     */
    public function query($sql);

    /**
     * Get a row for the result set
     *
     * @param resource $result
     * @param int $num
     * @param mixed $argument
     * @return array An associative array with each key representing a column
     */
    public function getRow($result, $num = 0, $argument = NULL);
}
