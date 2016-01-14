<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\Application\Setup;

use Netric\Application;
use Netric\Account;
use Netric\Account\Schema\SchemaDataMapperInterface;
use Netric\Error\AbstractHasErrors;

/**
 * Class for setting up an account on creation and for managing updates
 */
class Setup extends AbstractHasErrors
{
    /**
     * Initialize the account setup service
     */
    public function __construct()
    {
    }

    /**
     * Install application on local server
     */
    public function install()
    {
        // TODO: This will create the ansystem database and check the server
    }

    /**
     * Initialize a brand new account and create the admin user
     *
     * @param Account $account The new account to initialize
     * @param string $adminUserName Required username for the admin/first user
     * @param string $adminPassword Required password for the admin
     * @return bool true on success, false on failure - call $this->getLastError for details
     */
    public function setupAccount(Account $account, $adminUserName, $adminPassword)
    {
        $this->updateAccount($account);

        // TODO: Create admin user

        // TODO: Send new account registration to Aereus netric for admin

        return true;
    }

    /**
     * Update an existing account
     *
     * This function will make sure that an account is updated to the
     * latest version of the schema, configurations, and default data-sets.
     *
     * This account should already have been initialized!
     * @param Account $account The account to update
     * @return string The version we just updated to or null on failure
     */
    public function updateAccount(Account $account)
    {
        $schemaDataMapper = $account->getServiceManager()->get('Netric/Account/Schema/SchemaDataMapper');

        // Update or create the schema for this account
        if (!$schemaDataMapper->update($account->getId()))
        {
            // Die if we could not create the schema for the account
            throw new \RuntimeException("Cannot add account " . $schemaDataMapper->getLastError()->getMessage());
        }

        // Run all update scripts and return the last version run
        $updater = new AccountUpdater($account);
        $version = $updater->runUpdates();

        return $version;
    }
}
