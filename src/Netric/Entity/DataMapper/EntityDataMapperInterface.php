<?php

namespace Netric\Entity\DataMapper;

use Netric\Entity\EntityInterface;

/**
 * Entity DataMapper used for entity persistence
 */
interface EntityDataMapperInterface
{
    /**
     * Get an entity by unique id
     *
     * @param string $entityId The unique id of the entity to load
     * @return EntityInterface | null
     */
    public function getEntityById(string $entityId): ?EntityInterface;

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
    //public function getEntityByUniqueName($objType, $uniqueNamePath, array $namespaceFieldValues = []);

    /**
     * Delete an entity
     *
     * @param EntityInterface $entity The entity to delete
     * @return bool true on success, false on failure
     */
    //public function deleteEntity($entity): bool;

    /**
     * Save object data
     *
     * @param EntityInterface $entity The entity to save
     * @return string entity id on success, empty string on failure
     */
    public function saveEntity(EntityInterface $entity): string;

    /**
     * Point an old entity ID to a new entity ID (used with merging mostly)
     *
     * @param string $fromEntityId The id to move
     * @param string $toEntityId The unique id of the entity this was moved to
     * @return bool true on succes, false on failure
     */
    //public function setEntityMovedTo(string $fromEntityId, string $toEntityId): bool;

    /**
     * Check if an entity was moved to another object
     *
     * @param string $oldEntityId The id of the entity that no longer exists - may have moved
     * @return string unique id of new entity if the oldEntityId was previously moved
     */
    //public function getEntityMovedId(string $oldEntityId): string;

    /**
     * Get each entity revision
     *
     * @param string $entityId The unique id of the entity to get revisions for
     * @return array("revisionId"=>EntityInterface)
     */
    //public function getRevisions(string $entityId): array;
}
