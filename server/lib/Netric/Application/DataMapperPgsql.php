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
namespace Netric\Application;

use Netric\Db;

/**
 * Description of DataMapperPgsql
 *
 * @author Sky Stebnicki
 */
class DataMapperPgsql implements DataMapperInterface 
{
    /**
     * Host of db server
     * 
     * @var string
     */
    private $host = "";
    
    /**
     * Database name
     * 
     * @var string
     */
    private $database = "";
    
    /**
     * Db username
     * 
     * @var string
     */
    private $username = "";
    
    /**
     * Password for username
     * 
     * @var string
     */
    private $password = "";
    
    /**
     * Handle to database object
     * 
     * @var \CDatabase
     */
    private $dbh = null;
    
    /**
     * Connect to the pgsql database
     * 
     * @param string $host
     * @param string $database
     * @param string $username
     * @param string $password
     */
    public function __construct($host, $database, $username, $password) 
    {
        $this->host = $host;
        $this->database = $database;
        $this->username = $username;
        $this->password = $password;
        
        $this->dbh = new Db\Pgsql($host, $database, $username, $password);
    }
    
    /**
     * Get an account by id
     * 
     * @param string $id The unique id of the account to get
     * @param \Netric\Account $app Reference to Account object to initialize
     * @return bool true on success, false on failure/not found
     */
    public function getAccountById($id, &$account) 
    {
        $result = $this->dbh->query("SELECT * FROM accounts WHERE id=".$this->dbh->escapeNumber($id));
        if ($this->dbh->getNumRows($result))
        {
            $row = $this->dbh->getRow($result, 0);
            return $account->fromArray($row);
        }
        else
        {
            return false;
        }
    }
    
    /**
     * Get an account by the unique name
     * 
     * @param string $name
     * @param \Netric\Account $app Reference to Account object to initialize
     * @return bool true on success, false on failure/not found
     */
    public function getAccountByName($name, &$account)
    {
        $result = $this->dbh->query("SELECT * FROM accounts WHERE name='".$this->dbh->escape($name)."'");
        if ($this->dbh->getNumRows($result))
        {
            $row = $this->dbh->getRow($result, 0);
            return $account->fromArray($row);
        }
        else
        {
            return false;
        }
    }
    
    /**
     * Get an array of accounts
     * 
     * @param string $version If set the only get accounts that are at a current version
     * @return array
     */
    public function getAccounts($version="")
    {
        $ret = array();
        
        $sql = "SELECT * FROM accounts WHERE name='".$this->dbh->escape($name)."'";
        if ($version)
            $sql .= " AND version='" . $this->dbh->escape($version) . "'";
                
        $result = $this->dbh->query($sql);
        $num = $this->dbh->getNumRows($result);
        
        for ($i = 0; $i < $num; $i++)
        {
            $row = $this->dbh->getRow($result, $i);
            $ret[] = array(
                "id" => $row['id'],
                "name" => $row['name'],
                "database" => $row['database'],
            );
        }
        
        return $ret;
    }
}