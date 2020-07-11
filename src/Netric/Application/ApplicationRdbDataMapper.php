<?php

namespace Netric\Application;

use Netric\Account\Account;
use Netric\Error\ErrorAwareInterface;
use Netric\Error\Error;
use Netric\Db\Relational\PgsqlDb;
use Netric\Db\Relational\RelationalDbInterface;

/**
 * Access account data in a relational database
 */
class ApplicationRdbDataMapper implements DataMapperInterface, ErrorAwareInterface
{
    /**
     * Handle to database
     *
     * @var RelationalDbInterface
     */
    private $database = null;

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
    private $databaseName = "";

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
     * Errors array
     *
     * @var Error[]
     */
    private $errors = [];

    /**
     * Construct and initialize dependencies
     *
     * @param string $host
     * @param string $databaseName System database name
     * @param string $username System database username
     * @param string $password System database password
     */
    public function __construct(
        $host,
        $databaseName,
        $username,
        $password
    ) {
        $this->host = $host;
        $this->databaseName = $databaseName;
        $this->username = $username;
        $this->password = $password;

        // Create an instance of the new Relational Database of PgSql
        $this->database = new PgsqlDb(
            $this->host,
            $this->databaseName,
            $this->username,
            $this->password
        );
    }

    /**
     * Get an account by id
     *
     * @param string $id The unique id of the account to get
     * @param Account $account Reference to Account object to initialize
     * @return bool true on success, false on failure/not found
     */
    public function getAccountById($id, Account $account)
    {
        // Check first if we have database connection before getting the account data
        if (!$this->checkDbConnection()) {
            return false;
        }

        $sql = "SELECT * FROM accounts WHERE id=:id";
        $result = $this->database->query($sql, ["id" => $id]);

        if ($result->rowCount()) {
            $row = $result->fetch();
            return $account->fromArray($row);
        }

        return false;
    }

    /**
     * Get an account by the unique name
     *
     * @param string $name The name of the account that we will be getting
     * @param Account $account Reference to Account object to initialize if set
     * @return array|bool Return the account if found, false on failure/not found
     */
    public function getAccountByName($name, Account $account = null)
    {
        // Check first if we have database connection before getting the account data
        if (!$this->checkDbConnection()) {
            return false;
        }

        $sql = "SELECT * FROM accounts WHERE name=:name";
        $result = $this->database->query($sql, ["name" => $name]);

        if ($result->rowCount()) {
            $row = $result->fetch();

            if ($account) {
                return $account->fromArray($row);
            }

            return $row;
        }

        return false;
    }

    /**
     * Get an array of accounts
     *
     * @param string $version If set the only get accounts that are at a current version
     * @return array
     */
    public function getAccounts($version = "")
    {
        // Check first if we have database connection before getting the account data
        if (!$this->checkDbConnection()) {
            return false;
        }

        $ret = [];
        $sqlParams = [];

        $sql = "SELECT * FROM accounts WHERE active is not false";
        if (!empty($version)) {
            $sql .= " AND version=:version";
            $sqlParams["version"] = $version;
        }

        $result = $this->database->query($sql, $sqlParams);
        foreach ($result->fetchAll() as $row) {
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
        // Check first if we have database connection before getting the account data
        if (!$this->checkDbConnection()) {
            return false;
        }

        $ret = [];

        // Check accounts for a username matching this address
        $sql = "SELECT accounts.name as account, account_users.username
                                     FROM accounts, account_users WHERE
                                        accounts.id=account_users.account_id AND
                                        account_users.email_address=:email_address";

        $result = $this->database->query($sql, ["email_address" => strtolower($emailAddress)]);
        foreach ($result->fetchAll() as $row) {
            $ret[] = array(
                'account' => $row['account'],
                'username' => $row['username'],
            );
        }

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

        if (!is_numeric($accountId) || !$username) {
            return $ret;
        }

        // Delete any existing entries for this user name attached to this account
        $this->database->delete("account_users", ["account_id" => $accountId, "username" => $username]);

        // Insert into account_users table
        if ($emailAddress) {
            $insertData = [
                "account_id" => $accountId,
                "email_address" => $emailAddress,
                "username" => $username
            ];
            $result = $this->database->insert("account_users", $insertData);
            $ret = ($result) ? true : false;
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
        $insertData = [
            "name" => $name,
            //"database" => $this->defaultAccountDatabase,
        ];
        $ret = $this->database->insert("accounts", $insertData);

        if ($ret) {
            return $ret;
        }

        $this->errors[] = new Error("Could not create account in system database.");
        return 0;
    }

    /**
     * Delete an account by id
     *
     * @param $accountId The id of the account user is interacting with
     * @return bool true on success, false on failure - call getLastError for details
     */
    public function deleteAccount($accountId)
    {
        if (!is_numeric($accountId)) {
            throw new \RuntimeException("Account id must be a number");
        }

        // Remove any account users
        $this->database->delete("account_users", ["account_id" => $accountId]);

        // Now delete the actual account
        $ret = $this->database->delete("accounts", ["id" => $accountId]);

        if ($ret) {
            return true;
        }

        if ($ret === 0) {
            $this->errors[] = new Error("Accountid $accountId does not exists.");
        }

        return false;
    }

    /**
     * Get the last error (if any)
     *
     * @return Error | null
     */
    public function getLastError()
    {
        if (count($this->errors)) {
            return $this->errors[count($this->errors) - 1];
        } else {
            return null;
        }
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

    /**
     * Obtain a lock so that only one instance of a process can run at once
     *
     * @param string $uniqueLockName Globally unique lock name
     * @param int $expiresInSeconds Expire after defaults to 1 day or 86400 seconds
     * @return bool true if lock obtained, false if the process name is already locked (running)
     */
    public function acquireLock($uniqueLockName, $expiresInSeconds = 86400)
    {
        if (!$uniqueLockName) {
            throw new \InvalidArgumentException("Unique lock name is required to obtain a lock");
        }

        // Get the process lock
        $sql = "SELECT id, ts_entered FROM worker_process_lock " .
            "WHERE process_name=:process_name";

        $result = $this->database->query($sql, ["process_name" => $uniqueLockName]);
        if ($result->rowCount()) {
            $row = $result->fetch();
            $timeEntered = strtotime($row['ts_entered']);
            $now = time();

            // Check to see if the process has expired (run too long)
            if (($now - $timeEntered) >= $expiresInSeconds) {
                // Update the lock and return true so the caller can start a new process
                $ret = $this->database->update(
                    "worker_process_lock",
                    ["ts_entered" => date('Y-m-d H:i:s')],
                    ["id" => $row['id']]
                );

                if ($ret) {
                    return true;
                }
            }
        } else {
            $insertData = ["process_name" => $uniqueLockName, "ts_entered" => date('Y-m-d H:i:s')];
            $ret = $this->database->insert("worker_process_lock", $insertData);

            if ($ret) {
                return true;
            }
        }

        $this->errors[] = new Error("Could not create lock: $uniqueLockName");

        // The process is still legitimately running
        return false;
    }

    /**
     * Clear a lock so that only one instance of a process can run at once
     *
     * @param string $uniqueLockName Globally unique lock name
     */
    public function releaseLock($uniqueLockName)
    {
        $this->database->delete("worker_process_lock", ["process_name" => $uniqueLockName]);
    }

    /**
     * Refresh the lock to extend the expires timeout
     *
     * @param string $uniqueLockName Globally unique lock name
     * @return bool true on success, false on failure
     */
    public function extendLock($uniqueLockName)
    {
        $result = $this->database->update(
            "worker_process_lock",
            ["ts_entered" => date('Y-m-d H:i:s')],
            ["process_name" => $uniqueLockName]
        );
        return ($result) ? true : false;
    }

    /**
     * Closes the database connection
     */
    public function close()
    {
        $this->database->close();
    }

    /**
     * Function that will check if we have database connection
     *
     * @return bool Returns true if we have db connection otherwise returns false
     */
    private function checkDbConnection()
    {
        // If we do not have a database connection, then we log this as an error
        if (!$this->database->checkConnection()) {
            $this->errors["noDbConnection"] = new Error("There is no database connection.");
            return false;
        }

        return true;
    }
}
