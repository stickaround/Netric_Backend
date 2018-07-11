<?php

/**
 * Our implementation of a ServiceLocator pattern
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */

namespace Netric\ServiceManager;

use Netric\Account\Account;
use Netric\Entity\EntityLoaderFactory;
use Netric;

/**
 * Class for constructing, caching, and finding services by name
 */
class AccountServiceManager extends AbstractServiceManager implements AccountServiceManagerInterface
{
    /**
     * Handle to netric account
     *
     * @var Account
     */
    private $account = null;

    /**
     * Class constructor
     *
     * @param Account $account The account we are loading services for
     */
    public function __construct(Account $account)
    {
        $this->account = $account;
        $application = $account->getApplication();
        parent::__construct($application, $application->getServiceManager());
    }

    /**
     * Map a name to a class factory
     *
     * The target will be appended with 'Factory' so
     * "test" => "Netric/ServiceManager/Test/Service",
     * will load
     * Netric/ServiceManager/Test/ServiceFactory
     *
     * Use these sparingly because it does obfuscate from the
     * client what classes are being loaded.
     *
     * @var array
     */
    protected $invokableFactoryMaps = array(
        "test" => "Netric\\ServiceManager\\Test\\Service",
        "EntityFactory" => "Netric\\Entity\\EntityFactoryFactory",
        "RecurrenceDataMapper" => "Netric\\Entity\\Recurrence\\RecurrenceDataMapper",
        "RecurrenceIdentityMapper" => "Netric\\Entity\\Recurrence\\RecurrenceIdentityMapper",
        "Db" => "Netric\\Db\\Db",
        "Config" => "Netric\\Config\\Config",
        "Cache" => "Netric\\Cache\\Cache",
        "Entity_DataMapper" => "Netric\\Entity\\DataMapper\\DataMapper",
        "EntityDefinition_DataMapper" => "Netric\\EntityDefinition\\DataMapper\\DataMapper",
        "EntityDefinitionLoader" => "Netric\\EntityDefinition\\EntityDefinitionLoader",
        "EntityLoader" => EntityLoaderFactory::class,
        "EntitySync" => "Netric\\EntitySync\\EntitySync",
        "EntitySyncCommitManager" => "Netric\\EntitySync\\Commit\\CommitManager",
        "EntitySyncCommit_DataMapper" => "Netric\\EntitySync\\Commit\\DataMapper\\DataMapper",
        "EntitySync_DataMapper" => "Netric\\EntitySync\\DataMapper",
        "EntityGroupings_Loader" => "Netric\\EntityGroupings\\Loader",
        "Log" => "Netric\\Log\\Log",
        "EntityQuery_Index" => "Netric\\EntityQuery\\Index\\Index",
        "Entity_RecurrenceDataMapper" => "Netric\\Entity\\Recurrence\\RecurrenceDataMapper",
        "Application_DataMapper" => "Netric\\Application\\DataMapper"
    );

    /**
     * Get account instance of netric
     *
     * @return Account
     */
    public function getAccount()
    {
        return $this->account;
    }
}
