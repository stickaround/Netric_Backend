<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\Account;

use Netric\Account;

/**
 * Class for setting up an account
 *
 * Examples of how this is used in the real-world:
 *
 * // Create a new account - this should create any databases
 * $account = $application->createAccount("mytest");
 *
 * // Once an account has been created, it needs to be setup
 * $accountSetup = new Account\Setup();
 *
 * // To initialize a new account
 * $accountSetup->initialize($account, $adminUserName, $adminUserPassword);
 *
 * // To update an existing account
 * $accountSetup->update($account);
 */
class Setup
{
    /**
     * Initialize a brand new account and create the admin user
     *
     * @param Account $account The new account to initialize
     * @param string $adminUserName Required username for the admin/first user
     * @param string $adminPassword Required password for the admin
     */
    public function initialize(Account $account, $adminUserName, $adminPassword)
    {
    }

    /**
     * Update an existing account
     *
     * This function will make sure that an account is updated to the
     * latest version of the schema, configurations, and default data-sets.
     *
     * This account should already have been initialized!
     * @param Account $account The account to update
     */
    public function update(Account $account)
    {
    }
}
