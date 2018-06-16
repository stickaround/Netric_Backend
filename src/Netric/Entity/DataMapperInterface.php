<?php

/**
 * A DataMapper is responsible for writing and reading data from a persistant store
 *
 * @category    DataMapper
 * @author      Sky Stebnicki, sky.stebnicki@aereus.com
 * @copyright   Copyright (c) 2003-2013 Aereus Corporation (http://www.aereus.com)
 */
namespace Netric\Entity;

use Netric\Account\Account;
use Netric\EntityDefinition\EntityDefinition;

interface DataMapperInterface
{
    /**
     * Get an entity by id
     *
     * @param EntityInterface $entity The enitity to save
     * @param int $id The unique id of the entity to load
     * @return bool true if found and loaded successfully, false if not found or failed
     */
    public function getById(EntityInterface $entity, $id);

    /**
     * Get account associated with this request
     *
     * @return Account
     */
    public function getAccount();

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
    public function getByUniqueName($objType, $uniqueNamePath, array $namespaceFieldValues = []);

    /**
     * Delete an entity
     *
     * @param Entity $entity The enitity to save
     * @param bool $forceHard If true the data will be purged, if false first it will be archived
     * @return bool true on success, false on failure
     */
    public function delete(&$entity, $forceHard = false);

    /**
     * Save object data
     *
     * @param Entity $entity The entity to save
     * @return string|bool entity id on success, false on failure
     */
    public function save($entity);

    /**
     * Set this object as having been moved to another object
     *
     * @param EntityDefinition $def The defintion of this object type
     * @param string $fromId The id to move
     * @param stirng $toId The unique id of the object this was moved to
     * @return bool true on succes, false on failure
     */
    public function setEntityMovedTo(EntityDefinition &$def, $fromId, $toId);

    /**
     * Set this object as having been moved to another object
     *
     * @param EntityDefinition $def The defintion of this object type
     * @param string $fromId The id to move
     * @param stirng $toId The unique id of the object this was moved to
     * @return bool true on succes, false on failure
     */
    public function updateOldReferences(EntityDefinition $def, $fromId, $toId);

    /**
     * Check if an object has moved
     *
     * @param EntityDefinition $def The defintion of this object type
     * @param string $id The id of the object that no longer exists - may have moved
     * @return string|bool New entity id if moved, otherwise false
     */
    public function checkEntityHasMoved($def, $id);

    /**
     * Get Revisions for this object
     *
     * @param string $objType The name of the object type to get
     * @param string $id The unique id of the object to get revisions for
     * @return array("revisionNum"=>Entity)
     */
    public function getRevisions($objType, $id);
}
