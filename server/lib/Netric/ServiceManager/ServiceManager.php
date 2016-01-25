<?php
/**
 * Our implementation of a ServiceLocator pattern
 * 
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\ServiceManager;

use Netric;

/**
 * Class for constructing, caching, and finding services by name
 */
class ServiceManager implements ServiceLocatorInterface
{
    /**
	 * Handle to netric account
	 * 
	 * @var Netric\Account
	 */
	private $account = null;

	/**
	 * Cached services that have already been constructed
	 *
	 * @var array
	 */
	private $loadedServices = array();

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
    private $invokableFactoryMaps = array(
        // Test service map
        "test" => "Netric/ServiceManager/Test/Service",
        // The entity factory service will initialize new entities with injected dependencies
        "EntityFactory" => "Netric/Entity/EntityFactory",
        // The service required for saving recurring patterns
        "RecurrenceDataMapper" => "Netric/Entity/Recurrence/RecurrenceDataMapper",
        // IdentityMapper for loading/saving/caching RecurrencePatterns
        "RecurrenceIdentityMapper" => "Netric/Entity/Recurrence/RecurrenceIdentityMapper",
    );

    /**
	 * Class constructor
	 *
	 * We are private because the class must be a singleton to assure resources
	 * are initialized only once.
	 *
	 * @param Ant $ant The ant account we are loading services for
	 */
	public function __construct(\Netric\Account\Account $account)
	{
		$this->account = $account;
	}

	/**
	 * Get account instance of ANT
	 *
	 * @return Netric\Account
	 */
	public function getAccount()
	{
		return $this->account;
	}

	/**
	 * Get a service by name
	 *
	 * @param string $serviceName
	 * @return mixed The service object and false on failure
	 */
	public function get($serviceName)
	{
		$service = false;

		/*
		 * First check to see if we have a local factory function to load the service.
		 * This is the legacy way of loading services and this first if clause will
		 * eventually go away and just leave the 'else' code below.
		 */
		if (method_exists($this, "factory" . $serviceName))
        {
        	// Return cached version if already loaded
			if ($this->isLoaded($serviceName))
				return $this->loadedServices[$serviceName];

            $service = call_user_func(array($this, "factory" . $serviceName));

            // Cache the service
            if ($service)
            {
                $this->loadedServices[$serviceName] = $service;
            }
            else
            {
                throw new Exception\RuntimeException(sprintf(
                    '%s: A local factory function was found for "%s" but it did not return a valid service.',
                    get_class($this) . '::' . __FUNCTION__,
                    $serviceName
                ));
            }
        }
        else
        {
            $service = $this->initializeServiceByFactory($serviceName, true);
        }

		return $service;
	}

    /**
     * Attempt to initialize a service by loading a factory
     *
     * @param string $serviceName The class name of the service to load
     * @param bool $bCache Flag to enable caching this service for future requests
     * @throws Netric\ServiceManager\Exception\ServiceNotFoundException Could not autoload factory for named service
     * @return mixed Service instance if loaded, null if class not found
     */
    private function initializeServiceByFactory($serviceName, $bCache=true)
    {
        // First check to see if $sServiceName has been mapped to a factory
        $serviceName = $this->getInvokableTarget($serviceName);

        // Normalie the serviceName
        $serviceName = $this->normalizeClassPath($serviceName);

        // First check to see if the service was already loaded
        if ($this->isLoaded($serviceName))
			return $this->loadedServices[$serviceName];

        // Get actual class name by appending 'Factory' and normalizing slashes
        $classPath = $this->getServiceFactoryPath($serviceName);

		// Load the the service for the first time
        $service = null;

        // Try to load the service and allow exception to be thrown if not found
        if ($classPath)
        {
            if (class_exists($classPath))
            {
                $factory = new $classPath();
            }
            else
            {
                throw new Exception\ServiceNotFoundException(sprintf(
                    '%s: A service by the name "%s" was not found and could not be instantiated.',
                    get_class($this) . '::' . __FUNCTION__,
                    $classPath
                ));
            }


            if ($factory Instanceof ServiceFactoryInterface)
            {
                $service = $factory->createService($this);
            }
            else
            {
                throw new Exception\ServiceNotFoundException(sprintf(
                    '%s: The factory interface must implement Netric/ServiceManager/ServiceFactoryInterface.',
                    get_class($this) . '::' . __FUNCTION__,
                    $classPath
                ));
            }
        }

        // Cache for future calls
        if ($bCache)
        {
            $this->loadedServices[$serviceName] = $service;
        }

        return $service;
    }

    /**
     * Normalize class path
     *
     * @param string $classPath The unique name of the service to load
     */
    private function normalizeClassPath($classPath)
    {
        // Replace forward slash with backslash
        $classPath = str_replace('/', '\\', $classPath);

        // If class begins with "\Netric" then remove the first slash because it is not needed
        if ("\\Netric" == substr($classPath, 0 , strlen("\\Netric")))
        {
        	$classPath = substr($classPath, 1);
        }

        return $classPath;
    }

    /**
     * Try to locate service loading factory from the service path
     *
     * @param string $sServiceName The unique name of the service to load
     * @return string|bool The real path to the service factory class, or false if class not found
     */
    private function getServiceFactoryPath($sServiceName)
    {
        // Append Factory to the service name, then try to load using the initialized autoloaders
        $sClassPath = $sServiceName . "Factory";
        return $sClassPath;
    }

    /**
     * Check to see if a name is mapped to a real namespaced class
     *
     * @param string $sServiceName The potential service name alias
     * @return string If a map exists the rename the service to the real path, otherwise return the alias
     */
    private function getInvokableTarget($sServiceName)
    {
        if (isset($this->invokableFactoryMaps[$sServiceName]))
        {
            $sServiceName = $this->invokableFactoryMaps[$sServiceName];
        }

        return $sServiceName;
    }



    /**
	 * Check to see if a service is already loaded
	 *
	 * @param string $serviceName
	 * @return bool true if service is loaded and cached, false if it needs to be instantiated
	 */
	private function isLoaded($serviceName)
	{
		if (isset($this->loadedServices[$serviceName]) && $this->loadedServices[$serviceName] != null)
			return true;
		else
			return false;
	}

	/**
	 * Construct datamapper for an object type
	 *
	 * @param string $objType
	 * @return DataMapper
	 */ 
	private function factoryEntity_DataMapper()
    {
		// For now all we support is pgsql
		$dm = new Netric\Entity\DataMapper\Pgsql($this->getAccount(), $this->get("Db"));
		return $dm;
	}

    /**
	 * Construct and get handle to account database
	 *
	 * @return Netric\Db\DbInterface
	 */
	private function factoryDb()
	{
        
        // Setup antsystem datamapper
        $config = $this->get("Config");
        $db = new Netric\Db\Pgsql($config->db["host"], $this->getAccount()->getDatabaseName(), $config->db["user"], $config->db["password"]);
        $db->setSchema("acc_" . $this->getAccount()->getId());
		return $db;
	}
    
	/**
	 * Construct datamapper for an object type definition
	 *
	 * @return EntityDefinition_DataMapper
	 */
	private function factoryEntityDefinition_DataMapper()
	{
		// For now all we support is pgsql
		$dm = new Netric\EntityDefinition\DataMapper\Pgsql($this->getAccount(), $this->get("Db"));
		return $dm;
	}

    /**
	 * Construct entity definition loader
	 *
	 * @return EntityDefinitionLoader
	 */
	private function factoryEntityDefinitionLoader()
	{
		// For now all we support is pgsql
		$dm = $this->get("EntityDefinition_DataMapper");
        $cache = $this->get("Cache");
		$loader = new Netric\EntityDefinitionLoader($dm, $cache);
		return $loader;
	}

	/**
	 * Get config service
	 *
	 * @return AntConfig
	 */
	private function factoryConfig()
	{
		return $this->getAccount()->getApplication()->getConfig();
	}
    
    /**
	 * Get cache
	 *
	 * @return Netric\Cache\CacheInterface
	 */
	private function factoryCache()
	{
        return $this->getAccount()->getApplication()->getCache();
	}

	/**
	 * Get entity loader
	 *
	 * @return EntityLoader
	 */
	private function factoryEntityLoader()
	{
		$dm = $this->get("Entity_DataMapper");
		$definitionLoader = $this->get("EntityDefinitionLoader");

		$loader = new Netric\EntityLoader($dm, $definitionLoader);
		return $loader;
	}

	/**
	 * Get entity sync service
	 *
	 * @return Netric\EntitySync\EntitySync
	 */
	private function factoryEntitySync()
	{
		$dm = $this->get("EntitySync_DataMapper");
		$manager = new \Netric\EntitySync\EntitySync($dm);
		return $manager;
	}

	/**
	 * Get entity commit manager
	 *
	 * @return EntityLoader
	 */
	private function factoryEntitySyncCommitManager()
	{
		$dm = $this->get("EntitySyncCommit_DataMapper");
		$manager = new \Netric\EntitySync\Commit\CommitManager($dm);
		return $manager;
	}

	/**
	 * Get entity commit datamapper
	 *
	 * @return EntityLoader
	 */
	private function factoryEntitySyncCommit_DataMapper()
	{
		$dm = new \Netric\EntitySync\Commit\DataMapper\Pgsql($this->getAccount());
		return $dm;
	}

	/**
	 * Get entity commit datamapper
	 *
	 * @return EntityLoader
	 */
	private function factoryEntitySync_DataMapper()
	{
		$db = $this->get("Db");
		$dm = new \Netric\EntitySync\DataMapperPgsql($this->getAccount(), $db);
		return $dm;
	}
    
    /**
	 * Get entity loader
	 *
	 * @return EntityLoader
	 */
	private function factoryEntityGroupings_Loader()
	{
		$dm = $this->get("Entity_DataMapper");
        $cache = $this->get("Cache");
		$loader = new Netric\EntityGroupings\Loader($dm, $cache);
		return $loader;
	}
    
    /**
	 * Get the logger
	 *
	 * @return Log
	 */
	private function factoryLog()
	{
        return $this->getAccount()->getApplication()->getLog();
	}
    
    /**
	 * Get entity query index
	 *
	 * @return Netric\EntityQuery\IndexInterface
	 */
	private function factoryEntityQuery_Index()
	{
        return new \Netric\EntityQuery\Index\Pgsql($this->getAccount());
	}
    
     /**
	 * Get entity query index
	 *
	 * @return Netric\EntityQuery\IndexInterface
	 */
	private function factoryEntity_RecurrenceDataMapper()
	{
		$acct = $this->getAccount();
		$dbh = $this->get("Db");
        return new \Netric\Entity\Recurrence\RecurrenceDataMapper($acct, $dbh);
	}

    /**
     * Get the application datamapper
     *
     * @return Netric\Application\DataMapperInterface
     */
    private function factoryApplication_DataMapper()
    {
        $config = $this->get("Config");
        return new Netric\Application\DataMapperPgsql($config->db["host"],
                                                $config->db["sysdb"],
                                                $config->db["user"],
                                                $config->db["password"]);
    }

	/**
	 * Get DACL loader for security
	 *
	 * @return DaclLoader
	 */
	private function factoryDaclLoader()
	{
		return DaclLoader::getInstance($this->ant->dbh);
	}
    
    /**
	 * Get AntFs class
     * 
     * @deprecated This is legacy code used only for the entity datamapper at this point
	 *
	 * @return \AntFs 
	 */
	private function factoryAntFs()
	{
        require_once(dirname(__FILE__) . "/../../AntConfig.php");
        require_once(dirname(__FILE__) . "/../../CDatabase.awp");
        require_once(dirname(__FILE__) . "/../../Ant.php");
        require_once(dirname(__FILE__) . "/../../AntUser.php");
        require_once(dirname(__FILE__) . "/../../AntFs.php");

        $ant = new \Ant($this->getAccount()->getId());
        $user = $this->getAccount()->getUser();
        if (!$user)
            $user = $this->getAccount()->getUser(\Netric\UserEntity::USER_ANONYMOUS);
        $user = new \AntUser($ant->dbh, $user->getId(), $ant);
        $antfs = new \AntFs($ant->dbh, $user);
        
		return $antfs;
	}

	/**
	 * Get Help class
	 *
	 * @return Help 
	 */
	private function factoryHelp()
	{
		return new Help();
	}
}
