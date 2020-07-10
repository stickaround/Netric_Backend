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

        // Remove any email users
        $this->database->delete("email_users", ["account_id" => $accountId]);

        // Remove any email alias
        $this->database->delete("email_alias", ["account_id" => $accountId]);

        // Remove any email domains
        $this->database->delete("email_domains", ["account_id" => $accountId]);

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
     * Create a new email domain
     *
     * @param int $accountId The id of the account user is interacting with
     * @param string $domainName The domain we are going to create
     * @return bool true on success, false on failure
     */
    public function createEmailDomain($accountId, $domainName)
    {
        if (!$accountId || !$domainName) {
            throw new \InvalidArgumentException("accountId and domainName are required");
        }

        if ($this->getEmailDomain($accountId, $domainName)) {
            $this->errors[] = new Error("email domain: $domainName and account id: $accountId already exist");
            return false;
        }

        $insertData = [
            "domain" => $domainName,
            "account_id" => $accountId,
            "active" => true
        ];
        $this->database->insert("email_domains", $insertData);

        /*
         * After inserting the new email domain, we need to check if it successfully saved the data
         * $this->database->insert cannot return a value since email_domains table does not have a primary key
         */
        if ($this->getEmailDomain($accountId, $domainName)) {
            return true;
        }

        $this->errors[] = new Error("Error creating email domain: $domainName");
        return false;
    }

    /**
     * Function that will check if email domain and account id does exists
     *
     * @param $accountId The id of the account user is interacting with
     * @param $domainName The name of the domain we will be getting
     * @return array
     */
    public function getEmailDomain($accountId, $domainName)
    {
        if (!$accountId || !$domainName) {
            throw new \InvalidArgumentException("accountId and domainName are required");
        }

        $sql = "SELECT * FROM email_domains WHERE domain=:domain and account_id=:account_id";
        $result = $this->database->query($sql, ["domain" => $domainName, "account_id" => $accountId]);

        if ($result->rowCount()) {
            return $result->fetch();
        }

        return null;
    }

    /**
     * Delete an existing email domain
     *
     * @param int $accountId The id of the account user is interacting with
     * @param string $domainName The name of the domain that we will be deleting
     * @return bool true on success, false on failure
     */
    public function deleteEmailDomain($accountId, $domainName)
    {
        if (!$accountId || !$domainName) {
            throw new \InvalidArgumentException("accountId and domainName are required");
        }

        $ret = $this->database->delete("email_domains", ["domain" => $domainName, "account_id" => $accountId]);
        if (!$ret) {
            $this->errors[] = new Error("Error deleting domain name: $domainName");
            return false;
        }

        return true;
    }

    /**
     * Create or update an email alias
     *
     * @param int $accountId The id of the account user is interacting with
     * @param string $emailAddress The email address that we are going to create/update
     * @param string $goto The goto email address that we will be saving
     * @return bool true on success, false on failure
     */
    public function createOrUpdateEmailAlias($accountId, $emailAddress, $goto)
    {
        if (!$accountId || !$emailAddress || !$goto) {
            throw new \InvalidArgumentException("accountId and emailAddress and goto are required");
        }

        // TODO: Make sure the alias is for a domain we own

        $sql = "SELECT account_id FROM email_alias WHERE
                address=:address";

        $result = $this->database->query($sql, ["address" => $emailAddress]);
        if ($result->rowCount()) {
            $row = $result->fetch();

            // Check to make sure the accounts match
            if ($row['account_id'] != $accountId) {
                $this->errors[] = new Error("Could not update $emailAddress since it is owned by another account");
                return false;
            }

            $updateParams = ["address" => $emailAddress, "account_id" => $accountId];
            $this->database->update("email_alias", ["goto" => strtolower($goto)], $updateParams);
        } else {
            $insertData = [
                "address" => $emailAddress,
                "goto" => $goto,
                "active" => true,
                "account_id" => $accountId
            ];
            $this->database->insert("email_alias", $insertData);
        }

        $sql .= " and account_id=:account_id";
        $result = $this->database->query($sql, ["address" => $emailAddress, "account_id" => $accountId]);

        // Make sure that email_alias really exists for email_address and account_id
        if ($result->rowCount()) {
            return true;
        }

        $this->errors[] = new Error("Error on creating or updating an email alias: $emailAddress");
        return false;
    }

    /**
     * Delete an email alias
     *
     * @param int $accountId The id of the account user is interacting with
     * @param string $emailAddress The email address that will be using to delete the email alias entry
     * @return bool true on success, false on failure
     */
    public function deleteEmailAlias($accountId, $emailAddress)
    {
        if (!$accountId || !$emailAddress) {
            throw new \InvalidArgumentException("accountId and emailAddress are required");
        }

        $ret = $this->database->delete("email_alias", ["address" => $emailAddress, "account_id" => $accountId]);
        if (!$ret) {
            $this->errors[] = new Error("Error on deleting email alias: $emailAddress");
            return false;
        }

        return true;
    }

    /**
     * Create a new or update an existing email user in the mail system
     *
     * @param int $accountId The id of the account user is interacting with
     * @param string $emailAddress The email address that we are going to use to create/update an email user
     * @param string $password The password that we will be using
     * @return bool true on success, false on failure
     */
    public function createOrUpdateEmailUser($accountId, $emailAddress, $password)
    {
        if (!$accountId || !$emailAddress || !$password) {
            throw new \InvalidArgumentException("accountId and emailAddress and password are required");
        }

        // TODO: make sure its for a domain we manage

        $ret = null;
        $sql = "SELECT account_id FROM email_users WHERE
                email_address=:email_address";

        $result = $this->database->query($sql, ["email_address" => $emailAddress]);
        if ($result->rowCount()) {
            $row = $result->fetch();

            // Check to make sure the accounts match
            if ($row['account_id'] != $accountId) {
                $this->errors[] = new Error("Could not update $emailAddress since it is owned by another account");
                return false;
            }

            $updateParams = [
                "email_address" => $emailAddress,
                "account_id" => $accountId
            ];
            $ret = $this->database->update("email_users", ["password" => $password], $updateParams);
        } else {
            $inserData = [
                "email_address" => $emailAddress,
                "password" => $password,
                "maildir" => $emailAddress,
                "account_id" => $accountId
            ];
            $ret = $this->database->insert("email_users", $inserData);
        }

        if (!$ret) {
            $this->errors[] = new Error("Error on creating or updating an email user: $emailAddress");
            return false;
        }

        return true;
    }

    /**
     * Delete an email user from the mail system
     *
     * @param int $accountId The id of the account user is interacting with
     * @param string $emailAddress The email address that we will be using to delete an email
     * @return bool true on success, false on failure
     */
    public function deleteEmailUser($accountId, $emailAddress)
    {
        if (!$accountId || !$emailAddress) {
            throw new \InvalidArgumentException("accountId and emailAddress are required");
        }

        $deleteParams = ["email_address" => $emailAddress, "account_id" => $accountId];
        $ret = $this->database->delete("email_users", $deleteParams);
        if (!$ret) {
            $this->errors[] = new Error("Error on deleting an email user: $emailAddress");
            return false;
        }

        return true;
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
