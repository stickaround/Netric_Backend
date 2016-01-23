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
use Netric\Error\ErrorAwareInterface;
use Netric\Error\Error;

/**
 * Description of DataMapperPgsql
 *
 * @author Sky Stebnicki
 */
class DataMapperPgsql implements DataMapperInterface, ErrorAwareInterface
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
     * The default database name used for accounts
     *
     * At some point we may want to use different databases for different account
     * types or something like that, but for now we are putting everything in a common
     * database and utilizing PostgreSQL's schemas for multi-tenancy.
     *
     * @var null
     */
    private $defaultAccountDatabase = null;

    /**
     * Errors array
     *
     * @var Error[]
     */
    private $errors = [];
    
    /**
     * Connect to the pgsql database
     * 
     * @param string $host
     * @param string $database
     * @param string $username
     * @param string $password
     * @param string $defaultAccountDatabase The database name used for new accounts
     */
    public function __construct($host, $database, $username, $password, $defaultAccountDatabase = 'netric')
    {
        $this->host = $host;
        $this->database = $database;
        $this->username = $username;
        $this->password = $password;
        $this->defaultAccountDatabase = $defaultAccountDatabase;
        
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
        
        $sql = "SELECT * FROM accounts WHERE active is not false";
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

    /**
     * Get account and username from email address
     *
     * @param string $emailAddress The email address to pull from
     * @return array("account"=>"accountname", "username"=>"the login username")
     */
    public function getAccountsByEmail($emailAddress)
    {
        $ret = array();

        // All email addresses are stored in lower case
        $emailAddress = strtolower($emailAddress);

        // Check accounts for a username matching this address
        $result = $this->dbh->query("SELECT accounts.name as account, account_users.username 
                                     FROM accounts, account_users WHERE
                                        accounts.id=account_users.account_id AND 
                                        account_users.email_address='" . $this->dbh->escape($emailAddress) . "';");
        for ($i = 0; $i < $this->dbh->getNumRows($result); $i++)
        {
            $row = $this->dbh->getRow($result, $i);
            $ret[] = array(
                'account' => $row['account'],
                'username' => $row['username'],
            );
        }
        $this->dbh->freeResults($result);

        return $ret;
    }

    /**
     * Set account and username from email address
     *
     * @param int $accountId The id of the account user is interacting with
     * @param string $username The user name - unique to the account
     * @param string $emailAddress The email address to pull from
     * @return bool true on success, false on failure
     */
    public function setAccountUserEmail($accountId, $username, $emailAddress)
    {
        $ret = false;

        if (!is_numeric($accountId) || !$username)
            return $ret;

        // Delete any existing entries for this user name attached to this account
        $this->dbh->query("DELETE FROM account_users WHERE account_id='$accountId' AND 
                                    username='" . $this->dbh->escape($username) . "'");

        // Insert into account_users table
        if ($emailAddress)
        {
            $ret = $this->dbh->query("INSERT INTO account_users(account_id, email_address, username)
                                      VALUES(
                                        '$accountId', '" . $this->dbh->escape($emailAddress) . "', 
                                        '" . $this->dbh->escape($username) . "'
                                      );");
        }

        return $ret;
    }

    /**
     * Adds an account to the database
     *
     * @param string $name A unique name for this account
     * @return int Unique id of the created account, 0 on failure
     */
    public function createAccount($name)
    {
        // Create account in antsystem
        $ret = $this->dbh->query(
            "INSERT INTO accounts(name, database)
			 VALUES('".$this->dbh->escape($name)."', '".$this->dbh->escape($this->defaultAccountDatabase)."')
			 RETURNING id;");
        if ($this->dbh->getNumRows($ret))
        {
            return $this->dbh->getValue($ret, 0, "id");
        }

        $this->errors[] =  new Error("Could not create account in system database: " . $this->dbh->getLastError());
        return 0;
    }

    /**
     * Delete an account by id
     *
     * @param $accountId
     * @return bool true on success, false on failure - call getLastError for details
     */
    public function deleteAccount($accountId)
    {
        if (!is_numeric($accountId))
            throw new \RuntimeException("Account id must be a number");

        $ret = $this->dbh->query("DELETE FROM accounts WHERE id=" . $this->dbh->escapeNumber($accountId));
        if (!$ret)
            $this->errors[] = new Error("Error deleting account", $this->dbh->getLastError());

        return ($ret) ? true : false;
    }

    /**
     * Create the local database if it does not already exist
     *
     * @return bool true if exists, false if not and could not create it with $this->getLastError set
     */
    public function createDatabase()
    {
        // First try to connect to this database to see if it exists
        if ($this->dbh->connect())
            return true;

        // Try to create it by connecting to template1, then create the new db, and reconnect
        $template1 = new Db\Pgsql($this->host, "template1", $this->username, $this->password);
        if (!$template1->query("CREATE DATABASE " . $this->database))
        {
            $this->errors[] = new Error($this->dbh->getLastError());
            return false;
        }

        // New database was crated, now try to reconnect and return the results
        return ($this->dbh->connect()) ? true : false;
    }

    /**
     * Get the last error (if any)
     *
     * @return Error | null
     */
    public function getLastError()
    {
        if (count($this->errors))
            return $this->errors[count($this->errors) - 1];
        else
            return null;
    }

    /**
     * Get all errors
     *
     * @return \Netric\Error\Error[]
     */
    public function getErrors()
    {
        return $this->errors;
    }
}