<?php

namespace Netric\Entity\DataMapper;

use Netric\Account\Account;
use Netric\EntityDefinition\EntityDefinition;
use Netric\Entity\EntityInterface;
use Netric\Entity\ObjType\UserEntity;

/**
 * A DataMapper is responsible for writing and reading data from a persistant store
 */
interface EntityDataMapperInterface
{
    /**
     * Get an entity by id
     *
     * @param string $entityId The unique id of the entity to load
     * @param string $accountId
     * @return EntityInterface|null
     */
    public function getEntityById(string $entityId, string $accountId): ?EntityInterface;

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
     * @param string $accountId Current account ID
     * @param array $namespaceFieldValues Optional array of filter values for unique name namespaces
     * @return EntityInterface $entity if found or null if not found
     */
    public function getByUniqueName(
        string $objType,
        string $uniqueNamePath,
        string $accountId
    ): ?EntityInterface;

    /**
     * Archive an entity
     *
     * @param EntityInterface $entity The entity to delete
     * @param UserEntity $user The user that is acting on this entity
     * @return bool true on success, false on failure
     */
    public function archive(EntityInterface $entity, UserEntity $user): bool;

    /**
     * Delete an entity
     *
     * @param EntityInterface $entity The entity to delete
     * @param UserEntity $user The user that is acting on this entity
     * @return bool true on success, false on failure
     */
    public function delete(EntityInterface $entity, UserEntity $user): bool;

    /**
     * Save entity data
     *
     * @param EntityInterface $entity The entity to save
     * @param UserEntity $user The user that is acting on this entity
     * @return string entity id on success, false on failure
     */
    public function save(EntityInterface $entity, UserEntity $user): string;

    /**
     * Set this object as having been moved to another object
     *
     * @param string $fromId The id to move
     * @param string $toId The unique id of the object this was moved to
     * @param string $accountId
     * @return bool true on succes, false on failure
     */
    public function setEntityMovedTo(string $fromId, string $toId, string $accountId): bool;

    /**
     * Check if an object has moved
     *
     * @param EntityDefinition $def The defintion of this object type
     * @param string $id The id of the object that no longer exists - may have moved
     * @param string $accountId The ID of the account where the potentially moved entity belongs
     * @return string New entity id if moved, otherwise false
     */
    public function checkEntityHasMoved(EntityDefinition $def, string $entityId, string $accountId): string;

    /**
     * Get Revisions for this object
     *
     * @param string $entityId The unique id of the entity to get revisions for
     * @param string $accountId
     * @return array("revisionNum"=>Entity)
     */
    public function getRevisions(string $entityId, string $accontId): array;
}
