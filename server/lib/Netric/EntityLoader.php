<?php
/**
 * The identity map (loader) is responsible for loading a specific entity and caching it for future calls.
 */
namespace Netric;

class EntityLoader
{
	/**
 	 * Cached entities
	 */
	private $loadedEntities = array();

	/**
	 * Store the single instance of the loader 
	 */
    private static $m_pInstance;

	/**
	 * Datamapper for entities
	 *
	 * @var Entity_DataMapper
	 */
	private $dataMapper = null;

	/**
	 * Entity definition loader for getting definitions
	 *
	 * @var EntityDefinitionLoader
	 */
	private $definitionLoader = null;

	/**
	 * Cache
	 *
	 * @var Cache
	 */
	private $cache = null;

	/**
	 * Class constructor
	 *
	 * @param Entity_DataMapper $dm The entity datamapper
	 * @param EntityDefinitionLoader $defLoader The entity definition loader
	 */
	private function __construct(Entity\DataMapperInterface $dm, EntityDefinitionLoader $defLoader)
	{
		$this->dataMapper = $dm;
		$this->definitionLoader = $defLoader;
		$this->cache = $dm->getAccount()->getServiceManager()->get("Cache");
		return $this;
	}

	/**
	 * Factory
	 *
	 * @param Entity_DataMapperInterface $dm The entity datamapper
	 * @param EntityDefinitionLoader $defLoader The entity definition loader
	 */
	public static function getInstance(Entity\DataMapperInterface $dm, $defLoader) 
	{ 
		if (!self::$m_pInstance) 
			self::$m_pInstance = new EntityLoader($dm, $defLoader); 

		// If we have switched accounts then reload the cache
		if ($dm->getAccount()->getName() != self::$m_pInstance->dataMapper->getAccount()->getName())
		{
			self::$m_pInstance->loadedEntities = array(); 
			self::$m_pInstance->dataMapper = $dm; 
			self::$m_pInstance->definitionLoader = $defLoader; 
		}

		return self::$m_pInstance; 
	}

	/**
	 * Determine if an entity is already cached in memory
	 *
	 * @param string $id
	 * @return bool true if the entity was already loaded into memory, false if not
	 */
	private function isLoaded($objType, $id)
	{
		if (isset($this->loadedEntities[$objType][$id]) && $this->loadedEntities[$objType][$id] != null)
			return true;
		else
			return false;
	}

	/**
	 * Determine if an entity is in the cache layer
	 *
	 * @param string $objType The type of objet we are loading
	 * @param string $id
	 * @return array|bool Array of data if cached or false if nut found
	 */
	private function getCached($objType, $id)
	{
		return $this->cache->get($this->dataMapper->getAccount()->getName() . "/objects/" . $objType . "/" . $id);
	}

	/**
	 * Get the post by id from the datamapper
	 *
	 * @param string $objType The type of object we are getting
	 * @param string $id The unique id of the object
	 * @return Post
	 */
	public function get($objType, $id)
	{
		if ($this->isLoaded($objType, $id))
			return $this->loadedEntities[$objType][$id];

		// Create entity to load data into
		$entity = $this->create($objType);

		// First check to see if the object is cached
		$data = $this->getCached($objType, $id);
		if ($data)
		{
			$entity->fromArray($data);
			if ($entity->getId())
			{
				// Save in loadedEntities so we don't hit the cache again
				$this->loadedEntities[$objType][$id] = $entity;
				return $entity;
			}
		}

		// Load from datamapper
		if ($this->dataMapper->getById($entity, $id))
		{
			$this->loadedEntities[$objType][$id] = $entity;
			$this->cache->set($this->dataMapper->getAccount()->getName() . "/objects/" . $objType . "/" . $id, $entity->toArray());
			return $entity;
		}
		else
		{
			// TODO: make sure it is deleted from the index
		}

		return $entity;
	}

	/**
	 * Shortcut for constructing an Entity
	 *
	 * @param string $objType The name of the object type
	 * @return Entity|bool Eneity on success, false if definition does not exist for this entity
	 */
	public function create($objType)
	{
		$def = $this->definitionLoader->get($objType);
		
		if ($def->getId())
		{
			return Entity::factory($def);
		}
		else
		{
			return false;
		}
	}

	/**
	 * Clear cache
	 *
	 * @param string $objType The object type name
	 */
	public function clearCache($objType, $id)
	{
		if (isset($this->loadedEntities[$objType][$id]))
			$this->loadedEntities[$objType][$id] = null;

		$ret = $this->cache->remove($this->dataMapper->getAccount()->getName() . "/objects/" . $objType . "/" . $id);
	}
}
