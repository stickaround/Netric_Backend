<?php
namespace Netric\Application\Setup;

use Netric\Application\Application;
use Netric\Account\Account;
use Netric\Application\Schema\SchemaDataMapperFactory;
use Netric\Application\Schema\SchemaDataMapperInterface;
use Netric\Application\Schema\SchemaRdbDataMapper;
use Netric\Db\Relational\PgsqlDb;
use Netric\Error\AbstractHasErrors;
use Netric\Entity\EntityLoaderFactory;

/**
 * Class for setting up an account on creation and for managing updates
 */
class Setup extends AbstractHasErrors
{
    /**
     * Install application on local server
     *
     * @param Application $application Instance of application we are updateing
     * @return bool true on success, false on failure - call $this->getLastError for details
     */
    public function updateApplication(Application $application)
    {
        $schemaDataMapper = $this->getApplicationSchemaDataMapper($application);

        // Update the schema for this application
        if (!$schemaDataMapper->update()) {
            // Die if we could not create the schema for the account
            throw new \RuntimeException("Could not update application " . $schemaDataMapper->getLastError()->getMessage());
        }

        return true;
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
        $this->updateAccountSchema($account);

        // Fast forward this new account to the latest version and run always updates
        $updater = new AccountUpdater($account);
        $updater->setCurrentAccountToLatestVersion();
        $updater->runUpdates();

        // Create admin user
        $entityLoader = $account->getServiceManager()->get(EntityLoaderFactory::class);
        $adminUser = $entityLoader->create("user");
        $adminUser->setValue("name", $adminUserName);
        $adminUser->setValue("password", $adminPassword);
        $adminUser->setIsAdmin(true);
        $entityLoader->save($adminUser);

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
        $this->updateAccountSchema($account);

        // Run all update scripts and return the last version run
        $updater = new AccountUpdater($account);
        $version = $updater->runUpdates();

        return $version;
    }

    /**
     * Update an account to the latest schema
     *
     * @param Account $account The account to update
     */
    private function updateAccountSchema(Account $account): void
    {
        $schemaDataMapper = $account->getServiceManager()->get(SchemaDataMapperFactory::class);

        // Update or create the schema for this account
        if (!$schemaDataMapper->update($account->getId())) {
            // Die if we could not create the schema for the account
            throw new \RuntimeException("Cannot update account " . $schemaDataMapper->getLastError()->getMessage());
        }
    }

    /**
     * When working with an application we have to construct the DataMapper
     *
     * With accounts we can use the account ServiceLocator to setup the DataMapper
     * but with the Application there is no ServiceLocator so we have to setup the DataMapper
     * here manually. This will cause duplicate connections to the system database but that
     * should not be a problem since it will only be run once and is typically a background
     * process that is run from the command-line.
     *
     * @param Application $application
     * @return SchemaDataMapperInterface
     */
    private function getApplicationSchemaDataMapper(Application $application)
    {
        // Get application config
        $config = $application->getConfig();

        // Get the application definition
        $schemaDefinition = require(__DIR__ . "/../../../../data/schema/application.php");

        // Now get the system DataMapper
        switch ($config->db['type']) {
            case 'pgsql':
                // Get handle to system database
                $rdb = new PgsqlDb(
                    $config->db['syshost'],
                    $config->db['sysdb'],
                    $config->db['user'],
                    $config->db['password']
                );

                // Return DataMapper for this database type
                return new SchemaRdbDataMapper($rdb, $schemaDefinition);

                break;
            default:
                // Protect ourselves in the future to make sure new types are added here
                throw new \RuntimeException("Database type not yet supported: " . $config->db['type']);
        }
    }
}
