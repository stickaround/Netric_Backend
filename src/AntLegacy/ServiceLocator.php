<?php
/**
 * Service locator class abstracts constructing common services
 *
 * @category  ServiceLocator
 * @copyright Copyright (c) 2003-2013 Aereus Corporation (http://www.aereus.com)
 * @author Sky stebnicki, sky.stebnicki@aereus.com
 */
require_once("src/AntLegacy/AntLog.php");
require_once("src/AntLegacy/CDatabase.awp");
require_once("src/AntLegacy/AntSystem.php");
require_once("src/AntLegacy/CAntObject.php");
require_once("src/AntLegacy/aereus.lib.php/CCache.php");

// For datamapper factories
//require_once("src/AntLegacy/EntityLoader.php");
//require_once("src/AntLegacy/EntityDefinition/DataMapper/Pgsql.php");
//require_once("src/AntLegacy/Entity/DataMapper/Pgsql.php");
//require_once("src/AntLegacy/Entity/DataMapper/Pgsql.php");
// Other factories
require_once("src/AntLegacy/DaclLoader.php");
require_once("src/AntLegacy/Help.php");

use Netric\EntityQuery\Index\IndexFactory;
use Netric\EntityDefinition\DataMapper\DataMapperFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\Authentication\AuthenticationServiceFactory;
use Netric\Entity\EntityLoaderFactory;

/**
 * Base singleton class for service loader
 */
class ServiceLocator
{
	/**
	 * Handle to Ant account
	 * 
	 * @var Ant
	 */
	private $ant = null;

	/**
	 * Store the single instance
	 *
	 * @var ServiceLocator
	 */
	private static $_pInstance;

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
	private function __construct(&$ant)
	{
		$this->ant = $ant;
	}

	/**
	 * Factory
	 *
	 * @param Ant $ant The ant account we are loading services for
	 * @return ServiceLocator
	 */
	public static function getInstance(&$ant) 
	{
		if (!self::$_pInstance) 
			self::$_pInstance = new ServiceLocator($ant); 

		// If we have switched accounts then reload the cache
		if ($ant->id != self::$_pInstance->ant->id)
		{
			self::$_pInstance = null; 
			self::$_pInstance = new ServiceLocator($ant); 
			self::$_pInstance->loadedServices = array(); 
		}

		return self::$_pInstance; 
	}

	/**
	 * Get account instance of ANT
	 *
	 * @return Ant
	 */
	public function getAnt()
	{
		return $this->ant;
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
        $acc = $this->getAnt()->getNetricAccount(); 
        if (!$acc)
        	throw new \Exception("Could not get the account. Aborting!");

        return $acc->getServiceManager()->get(IndexFactory::class);
        /*
		// For now all we support is pgsql
		$dm = new Entity_DataMapper_Pgsql($this, $this->ant->id);
		return $dm;
         */
	}

	/**
	 * Construct datamapper for an object type definition
	 *
	 * @return EntityDefinition_DataMapper
	 */
	private function factoryEntityDefinition_DataMapper()
	{
        $acc = $this->getAnt()->getNetricAccount(); 
        if (!$acc)
        	throw new \Exception("Could not get the account. Aborting!");

        return $acc->getServiceManager()->get(DataMapperFactory::class);
		// For now all we support is pgsql
		//$dm = new EntityDefinition_DataMapper_Pgsql($this->ant->id, $this->ant->dbh);
		//return $dm;
	}

	/**
	 * Construct collection index
	 *
	 * @return EntityCollection_IndexInterface
	 */
	private function factoryEntityCollection_Index()
	{
		// TODO: construct index
	}

	/**
	 * Construct entity definition loader
	 *
	 * @return EntityDefinition_DataMapper
	 */
	private function factoryEntityDefinitionLoader()
	{
        $acc = $this->getAnt()->getNetricAccount(); 
        if (!$acc)
        	throw new \Exception("Could not get the account. Aborting!");

        return $acc->getServiceManager()->get(EntityDefinitionLoaderFactory::class);
		// For now all we support is pgsql
		//$dm = $this->get("EntityDefinition_DataMapper");
		//$loader = new EntityDefinitionLoader($dm);
		//return $loader;
	}

	/**
	 * Construct entity definition loader
	 *
	 * @return EntityDefinition_DataMapper
	 */
	private function factoryAuthenticationService()
	{
        $acc = $this->getAnt()->getNetricAccount(); 
        if (!$acc)
        	throw new \Exception("Could not get the account. Aborting!");

        return $acc->getServiceManager()->get(AuthenticationServiceFactory::class);
		// For now all we support is pgsql
		//$dm = $this->get("EntityDefinition_DataMapper");
		//$loader = new EntityDefinitionLoader($dm);
		//return $loader;
	}

	/**
	 * Get config service
	 *
	 * @return AntConfig
	 */
	private function factoryConfig()
	{
		return AntConfig::getInstance();
	}

	/**
	 * Get entity loader
	 *
	 * @return EntityLoader
	 */
	private function factoryEntityLoader()
	{
        $acc = $this->getAnt()->getNetricAccount(); 
        if (!$acc)
        	throw new \Exception("Could not get the account. Aborting!");

        return $acc->getServiceManager()->get(EntityLoaderFactory::class);
		//$dm = $this->get("Entity_DataMapper");
		//$definitionLoader = $this->get("EntityDefinitionLoader");
		//$loader = EntityLoader::getInstance($dm, $definitionLoader);
		//return $loader;
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
	 * Get entity query index
	 *
	 * @return EntityQuery\IndexInterface
	 */
	private function factoryEntityQuery_Index()
	{
		$acc = $this->getAnt()->getNetricAccount();
		return $acc->getServiceManager()->get(IndexFactory::class);
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
	
	/**
	 * Get AntFs class
	 *
	 * @return \AntFs
	 */
	private function factoryAntFs()
	{	
		$user = $this->getAnt()->getUser();
		if (!$user) {
		    $entityLoader = $this->factoryEntityLoader();
            $userEntity = $entityLoader->getUser(Netric\Entity\ObjType\UserEntity::USER_ANONYMOUS);
            $user = $this->getAnt()->getUser($userEntity->getId());
        }

		
		$user = new AntUser($this->getAnt()->dbh, $user->getId(), $this->getAnt());
		$antfs = new AntFs($this->getAnt()->dbh, $user);
	
		return $antfs;
	}
}