<?php

namespace Netric\Entity;

use Netric\Stats\StatsPublisher;
use Netric\Cache\CacheInterface;
use Netric\EntityDefinition\EntityDefinitionLoader;
use Netric\Entity\DataMapper\EntityDataMapperInterface;
use Netric\Entity\Entity;
use Netric\Entity\ObjType\UserEntity;
use Ramsey\Uuid\Uuid;


/**
 * Entity service used to get/save/delete entities
 */
class EntityLoader
{
    /**
     * Cached entities
     *
     * @var EntityInterface[]
     */
    private $loadedEntities = [];

    /**
     * Datamapper for entities
     *
     * @var EntityDataMapperInterface
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
     * @param EntityDataMapperInterface $dataMapper The entity datamapper
     * @param EntityDefinitionLoader $defLoader The entity definition loader
     * @param EntityFactory $entityFactory
     * @param CacheInterface $cache
     */
    public function __construct(
        EntityDataMapperInterface $dataMapper,
        EntityDefinitionLoader $defLoader,
        EntityFactory $entityFactory,
        CacheInterface $cache
    ) {
        $this->dataMapper = $dataMapper;
        $this->definitionLoader = $defLoader;
        $this->entityFactory = $entityFactory;
        $this->cache = $cache;
    }

    /**
     * Determine if an entity is already cached in memory
     *
     * @param string $entityId
     * @return bool true if the entity was already loaded into memory, false if not
     */
    private function isLoaded(string $entityId)
    {
        return (!empty($this->loadedEntities[$entityId])) ? true : false;
    }

    /**
     * Determine if an entity is in the cache layer
     *
     * @param string $objType The type of objet we are loading
     * @param string $id
     * @return array|bool Array of data if cached or false if nut found
     */
    private function getCached(string $entityId)
    {
        $key = "entity/" . $entityId;
        return $this->cache->get($key);
    }

    /**
     * Get an entity by the global universal ID (no need for obj_type)
     *
     * @param string $entityId
     * @param string $accountId
     * @return EntityInterface|null
     */
    public function getEntityById(string $entityId, string $accountId): ?EntityInterface
    {
        if ($this->isLoaded($entityId)) {
            return $this->loadedEntities[$entityId];
        }

        // First check to see if the object is cached
        $data = $this->getCached($entityId);
        if ($data && isset($data['obj_type'])) {
            $entity = $this->create($data['obj_type'], $accountId);
            $entity->fromArray($data);
            if ($entity->getEntityId()) {
                // Clear dirty status
                $entity->resetIsDirty();

                // Save in loadedEntities so we don't hit the cache again
                $this->loadedEntities[$entityId] = $entity;

                // Stat a cache hit
                StatsPublisher::increment("entity.cache.hit");

                return $entity;
            }
        }

        // Stat a cache miss
        StatsPublisher::increment("entity.cache.miss");

        // Load from datamapper
        $entity = $this->dataMapper->getEntityById($entityId, $accountId);
        if ($entity) {
            $this->loadedEntities[$entityId] = $entity;
            $this->cache->set(
                "entity/" . $entityId,
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
     * @param string $accountId The current accountId
     * @param array $namespaceFieldValues Optional array of filter values for unique name namespaces
     * @return EntityInterface $entity if found or null if not found
     */
    public function getByUniqueName(string $objType, string $uniqueNamePath, string $accountId, array $namespaceFieldValues = [])
    {
        // TODO: We should definitely handle caching here since this function can be expensive
        return $this->dataMapper->getByUniqueName($objType, $uniqueNamePath, $accountId, $namespaceFieldValues);
    }

    /**
     * Shortcut for constructing an Entity
     *
     * @param string $definitionName The name of the entity definition
     * @param string $accountId The account ID that will own the entity
     * @return EntityInterface
     */
    public function create(string $definitionName, string $accountId)
    {
        return $this->entityFactory->create($definitionName, $accountId);
    }

    /**
     * Save an entity
     *
     * @param EntityInterface $entity The entity to save
     * @param UserEntity Entity with user details
     * @return int|string|null Id of entity saved or null on failure
     */
    public function save(EntityInterface $entity, UserEntity $user)
    {
        $ret = $this->dataMapper->save($entity, $user);

        // Also clear the cache for entity id
        if ($ret) {
            $this->clearCacheByGuid($ret);
        }

        return $ret;
    }

    /**
     * Save an entity
     *
     * @param EntityInterface $entity The entity to delete
     * @param UserEntity Entity with user details
     * @return bool True on success, false on failure
     */
    public function delete(EntityInterface $entity, UserEntity $user)
    {
        $this->clearCacheByGuid($entity->getEntityId());

        return $this->dataMapper->delete($entity, $user);
    }

    /**
     * Flag entity as archived but don't actually delete it
     *
     * @param EntityInterface $entity The entity to delete
     * @param UserEntity Entity with user details
     * @return bool True on success, false on failure
     */
    public function archive(EntityInterface $entity, UserEntity $user)
    {
        $this->clearCacheByGuid($entity->getEntityId());

        return $this->dataMapper->archive($entity, $user);
    }

    /**
     * Clear cache by guid
     *
     * @param string $guid The guid of an entity
     */
    public function clearCacheByGuid(string $guid)
    {
        if ($guid) {
            $this->loadedEntities[$guid] = null;
            $this->cache->remove("entity/$guid");
        }
    }

    /**
     * Get Revisions for this object
     *
     * @param string $objType The name of the object type to get
     * @param string $guid The unique id of the object to get revisions for
     * @return array("revisionNum"=>Entity)
     */
    public function getRevisions(string $entityId, string $accountId): array
    {
        return $this->dataMapper->getRevisions($entityId, $accountId);
    }
}
