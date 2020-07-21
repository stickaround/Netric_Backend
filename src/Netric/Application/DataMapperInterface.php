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

use Netric\Account\Account;
use DateTime;

/**
 * Description of DataMapperInterface
 *
 * @author Sky Stebnicki
 */
interface DataMapperInterface
{
    /**
     * Get an account by id
     *
     * @param string $id The unique id of the account to get
     * @param \Netric\Account\Account $account Reference to Account object to initialize
     * @return bool true on success, false on failure/not found
     */
    public function getAccountById($id, Account $account);

    /**
     * Get an account by the unique name
     *
     * @param string $name
     * @param \Netric\Account\Account $account Reference to Account object to initialize
     * @return bool true on success, false on failure/not found
     */
    public function getAccountByName($name, Account $account = null);

    /**
     * Get an associative array of account data
     *
     * @param string $version If set the only get accounts that are at a current version
     * @return array
     */
    public function getAccounts($version = "");

    /**
     * Get account and username from email address
     *
     * @param string $emailAddress The email address to pull from
     * @return array("account"=>"accountname", "username"=>"the login username")
     */
    public function getAccountsByEmail($emailAddress);

    /**
     * Set account and username from email address
     *
     * @param string $accountId The id of the account user is interacting with
     * @param string $username The user name - unique to the account
     * @param string $emailAddress The email address to pull from
     * @return bool true on success, false on failure
     */
    public function setAccountUserEmail(string $accountId, $username, $emailAddress);

    /**
     * Adds an account to the database
     *
     * @param string $name A unique name for this account
     * @return int Unique id of the created account
     */
    public function createAccount($name);

    /**
     * Delete an account by id
     *
     * @param string $accountId
     * @return bool true on success, false on failure - call getLastError for details
     */
    public function deleteAccount(string $accountId): bool;

    /**
     * Obtain a lock so that only one instance of a process can run at once
     *
     * @param string $uniqueLockName Globally unique lock name
     * @param int $expiresInSeconds Expire after defaults to 1 day or 86400 seconds
     * @return bool true if lock obtained, false if the process name is already locked (running)
     */
    public function acquireLock($uniqueLockName, $expiresInSeconds = 86400);

    /**
     * Clear a lock so that only one instance of a process can run at once
     *
     * @param string $uniqueLockName Globally unique lock name
     * @return bool true on success, false on failure
     */
    public function releaseLock($uniqueLockName);

    /**
     * Refresh the lock to extend the expires timeout
     *
     * @param string $uniqueLockName Globally unique lock name
     * @return bool true on success, false on failure
     */
    public function extendLock($uniqueLockName);
}
