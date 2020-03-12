<?php
namespace Netric\Entity;

use Netric\Stats\StatsPublisher;
use Netric\Cache\CacheInterface;
use Netric\EntityDefinition\EntityDefinitionLoader;
use Netric\Entity\Entity;
use Ramsey\Uuid\Uuid;

/**
 * The identity map (loader) is responsible for loading a specific entity and caching it for future calls.
 */
class EntityLoader
{
    /**
     * Cached entities
     *
     * @var EntityInterface[]
     */
    private $loadedEntities = array();

    /**
     * Store the single instance of the loader
     */
    private static $m_pInstance;

    /**
     * Datamapper for entities
     *
     * @var DataMapperInterface
     */
    private $dataMapper = null;

    /**
     * Entity definition loader for getting definitions
     *
     * @var EntityDefinitionLoader
     */
    private $definitionLoader = null;

    /**
     * Entity factory used for instantiating new entities
     *
     * @var \Netric\Entity\EntityFactory
     */
    protected $entityFactory = null;

    /**
     * Cache
     *
     * @var CacheInterface
     */
    private $cache = null;

    /**
     * Class constructor
     *
     * @param DataMapperInterface $dm The entity datamapper
     * @param EntityDefinitionLoader $defLoader The entity definition loader
     * @param EntityFactory $entityFactory
     * @param CacheInterface $cache
     */
    public function __construct(
        DataMapperInterface $dm,
        EntityDefinitionLoader $defLoader,
        EntityFactory $entityFactory,
        CacheInterface $cache
    ) {
        $this->dataMapper = $dm;
        $this->definitionLoader = $defLoader;
        $this->entityFactory = $entityFactory;
        $this->cache = $cache;
        return $this;
    }

    /**
     * Singleton factory
     *
     * This will be deprecated when we no longer need to support it in legacy code
     *
     * @param DataMapperInterface $dm The entity datamapper
     * @param EntityDefinitionLoader $defLoader The entity definition loader
     * @return EntityLoader
     */
    public static function getInstance(DataMapperInterface $dm, $defLoader)
    {
        if (!self::$m_pInstance) {
            self::$m_pInstance = new EntityLoader($dm, $defLoader);
        }

        // If we have switched accounts then reload the cache
        if ($dm->getAccount()->getName() != self::$m_pInstance->dataMapper->getAccount()->getName()) {
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
        if (isset($this->loadedEntities[$objType][$id]) && $this->loadedEntities[$objType][$id] != null) {
            return true;
        } else {
            return false;
        }
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
        $key = $this->dataMapper->getAccount()->getName() . "/objects/$objType/$id";
        return $this->cache->get($key);
    }

    /**
     * Get the post by id from the datamapper
     *
     * @param string $objType The type of object we are getting
     * @param string $id The unique id of the object
     * @param EntityInterface $entityToFill Optional entity to fill rather than creating a new one
     * @return EntityInterface
     */
    public function get($objType, $id, EntityInterface $entityToFill = null)
    {
        /*
         * We need to check if the id provided here is a guid or just an id
         * With the latest update made in object references, we are now using the entity's guid instead of id
         * Once we have fully migrated to guid and updated all the entities to use guid, then we can remove this function
         *  and just used the $this->getByGuid() - Marl 02/14/2020
         */
        if (Uuid::isValid($id)) {
            return $this->getByGuid($id);
        }
        
        if ($this->isLoaded($objType, $id)) {
            return $this->loadedEntities[$objType][$id];
        }

        // Create entity to load data into
        $entity = ($entityToFill) ? $entityToFill : $this->create($objType);

        // First check to see if the object is cached
        $data = $this->getCached($objType, $id);
        if ($data) {
            $entity->fromArray($data);
            if ($entity->getId()) {
                // Clear dirty status
                $entity->resetIsDirty();

                // Save in loadedEntities so we don't hit the cache again
                $this->loadedEntities[$objType][$id] = $entity;

                // Stat a cache hit
                StatsPublisher::increment("entity.cache.hit");

                return $entity;
            }
        }

        // Stat a cache miss
        StatsPublisher::increment("entity.cache.miss");

        // Load from datamapper
        if ($this->dataMapper->getById($entity, $id)) {
            $this->loadedEntities[$objType][$id] = $entity;
            $this->cache->set($this->dataMapper->getAccount()->getName() . "/objects/$objType/$id", $entity->toArray());
            return $entity;
        } else {
            // TODO: make sure it is deleted from the index?
        }

        // Could not be loaded
        return null;
    }

    /**
     * Get an entity by the global universal ID (no need for obj_type)
     *
     * @param string $guid
     * @return EntityInterface|null
     */
    public function getByGuid(string $guid): ? EntityInterface
    {
        if ($this->isLoaded('guid', $guid)) {
            return $this->loadedEntities['guid'][$guid];
        }

        // First check to see if the object is cached
        $data = $this->getCached('guid', $guid);
        if ($data && isset($data['obj_type'])) {
            $entity = $this->create($data['obj_type']);
            $entity->fromArray($data);
            if ($entity->getId()) {
                // Clear dirty status
                $entity->resetIsDirty();

                // Save in loadedEntities so we don't hit the cache again
                $this->loadedEntities['guid'][$guid] = $entity;

                // Stat a cache hit
                StatsPublisher::increment("entity.cache.hit");

                return $entity;
            }
        }

        // Stat a cache miss
        StatsPublisher::increment("entity.cache.miss");

        // Load from datamapper
        $entity = $this->dataMapper->getByGuid($guid);
        if ($entity) {
            $this->loadedEntities['guid'][$guid] = $entity;
            $this->cache->set(
                $this->dataMapper->getAccount()->getName() . "/objects/guid/$guid",
                $entity->toArray()
            );
            return $entity;
        }

        // TODO: make sure it is deleted from the index?

        // Could not be loaded
        return null;
    }

    /**
     * Get an entity by a unique name path
     *
     * Unique names can be namespaced, and we can reference entities with a full
     * path since the namespace can be a parentField. For example, the 'page' entity
     * type has a unique name namespace of parentId so we could path /page1/page2/page1
     * and the third page1 is a different entity than the first.
     *
     * @param string $objType The entity to populate if we find the data
     * @param string $uniqueNamePath The path to the entity
     * @param array $namespaceFieldValues Optional array of filter values for unique name namespaces
     * @return EntityInterface $entity if found or null if not found
     */
    public function getByUniqueName($objType, $uniqueNamePath, array $namespaceFieldValues = [])
    {
        // TODO: We should definitely handle caching here since this function can be expensive
        return $this->dataMapper->getByUniqueName($objType, $uniqueNamePath, $namespaceFieldValues);
    }

    /**
     * Shortcut for constructing an Entity
     *
     * @param string $objType The name of the object type
     * @return \Netric\Entity\EntityInterface
     */
    public function create($objType)
    {
        return $this->entityFactory->create($objType);
    }

    /**
     * Save an entity
     *
     * @param EntityInterface $entity The entity to save
     * @return int|string|null Id of entity saved or null on failure
     */
    public function save(EntityInterface $entity)
    {
        $ret = $this->dataMapper->save($entity);

        if ($entity->getId()) {
            $this->clearCache($entity->getDefinition()->getObjtype(), $entity->getId());
        }

        // Also clear the cache for entity guid
        $this->clearCacheByGuid($entity->getGuid());

        return $ret;
    }

    /**
     * Save an entity
     *
     * @param EntityInterface $entity The entity to delete
     * @param bool $forceHard If true the force a hard delete - purge!
     * @return bool True on success, false on failure
     */
    public function delete(EntityInterface $entity, $forceHard = false)
    {
        $this->clearCache($entity->getDefinition()->getObjType(), $entity->getId());

        return $this->dataMapper->delete($entity, $forceHard);
    }

    /**
     * Clear cache
     *
     * @param string $objType The object type name
     * @param int $id The id of the entity to clear
     */
    public function clearCache($objType, $id)
    {
        if (isset($this->loadedEntities[$objType][$id])) {
            $this->loadedEntities[$objType][$id] = null;
        }

        $ret = $this->cache->remove($this->dataMapper->getAccount()->getName() . "/objects/$objType/$id");
    }

    /**
     * Clear cache by guid
     * 
     * @param string $guid The guid of an entity
     */
    public function clearCacheByGuid(string $guid) {
        if ($guid) {
            $this->loadedEntities['guid'][$guid] = null;
            $this->cache->remove($this->dataMapper->getAccount()->getName() . "/objects/guid/$guid");
        }
    }

    /**
     * Clear any cache and reload from the database to make sure we have the latest version
     *
     * @param EntityInterface $entity The entity to refresh
     * @throws \RuntimeException If an invalid entity was passed in
     */
    public function reload(EntityInterface $entity)
    {
        if (!$entity->getObjType() || !$entity->getId()) {
            throw new \RuntimeException("Cannot refresh an entity that was not saved - no obj_type or ID");
        }
        $this->clearCache($entity->getObjType(), $entity->getId());
        $this->get($entity->getObjType(), $entity->getId(), $entity);
    }

    /**
     * Get Revisions for this object
     *
     * @param string $objType The name of the object type to get
     * @param string $id The unique id of the object to get revisions for
     * @return array("revisionNum"=>Entity)
     */
    public function getRevisions($objType, $id)
    {
        return $this->dataMapper->getRevisions($objType, $id);
    }

    /**
     * Function that will check if the value is a valid uuid or an object reference. Then will return the entity
     * 
     * @param string $value This value should be either a valid guid or an entity object reference
     * @param string $objType Optiional. If the value provided is an entity id, then we need an object type to retrieve the entity
     */
    public function getByGuidOrObjRef(string $value, string $objType = "")
    {
        // We need to check first if the value is already a guid
        if (Uuid::isValid($value)) {
            return $this->getByGuid($value);
        } else if (is_numeric($value) && $objType) {
            return $this->get($objType, $value);
        } else {
            $parts = Entity::decodeObjRef($value);
            if (isset($parts['obj_type']) && isset($parts['id'])) {
                return $this->get($parts['obj_type'], $parts['id']);
            }
        }

        return null;
    }
}
