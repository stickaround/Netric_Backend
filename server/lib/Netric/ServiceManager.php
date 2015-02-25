<?php
/*
 * Our implementation of a ServiceLocator pattern
 * 
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2014 Aereus
 */
namespace Netric;

/**
 * Class for constructing, caching, and finding services by name
 */
class ServiceManager 
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
	 * Class constructor
	 *
	 * We are private because the class must be a singleton to assure resources
	 * are initialized only once.
	 *
	 * @param Ant $ant The ant account we are loading services for
	 */
	public function __construct(Account $account)
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
		// Return cached version if already loaded
		if ($this->isLoaded($serviceName))
			return $this->loadedServices[$serviceName];

		$service = false;

		// Run the service factory function
		if (method_exists($this, "factory" . $serviceName))
			$service = call_user_func(array($this, "factory" . $serviceName));

		// Cache the service
		if ($service)
			$this->loadedServices[$serviceName] = $service;

		return $this->loadedServices[$serviceName];
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
		$dm = new Entity\DataMapper\Pgsql($this->getAccount(), $this->get("Db"));
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
        $db = new Db\Pgsql($config->db["host"], $this->getAccount()->getDatabaseName(), $config->db["user"], $config->db["password"]);
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
		$dm = new EntityDefinition\DataMapper\Pgsql($this->getAccount(), $this->get("Db"));
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
		$loader = new EntityDefinitionLoader($dm, $cache);
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
        $cache = new Cache\AlibCache();
		return $cache;
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
		$loader = EntityLoader::getInstance($dm, $definitionLoader);
		return $loader;
	}

	/**
	 * Get entity sync service
	 *
	 * @return EntitySync\EntitySync
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
		$loader = new EntityGroupings\Loader($dm, $cache);
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
	 * @return EntityQuery\IndexInterface
	 */
	private function factoryEntityQuery_Index()
	{
        return new \Netric\EntityQuery\Index\Pgsql($this->getAccount());
	}
    
     /**
	 * Get entity query index
	 *
	 * @return EntityQuery\IndexInterface
	 */
	private function factoryEntity_RecurrenceDataMapper()
	{
		$acct = $this->getAccount();
		$dbh = $this->get("Db");
        return new \Netric\Entity\Recurrence\RecurrenceDataMapper($acct, $dbh);
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
        require_once(dirname(__FILE__) . "/../AntConfig.php");
        require_once(dirname(__FILE__) . "/../CDatabase.awp");
        require_once(dirname(__FILE__) . "/../Ant.php");
        require_once(dirname(__FILE__) . "/../AntUser.php");
        require_once(dirname(__FILE__) . "/../AntFs.php");

        $ant = new \Ant($this->getAccount()->getId());
        $user = $this->getAccount()->getUser();
        if (!$user)
            $user = $this->getAccount()->getUser(User::USER_ANONYMOUS);
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
