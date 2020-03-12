<?php

namespace Netric\Entity;

use Netric\EntityDefinition\Exception\DefinitionStaleException;
use Netric\Entity\Recurrence\RecurrenceIdentityMapper;
use Netric\EntityQuery;
use Netric\EntitySync\Commit\CommitManager;
use Netric\EntitySync\EntitySyncFactory;
use Netric\EntitySync\EntitySync;
use Netric\Entity\Recurrence\RecurrenceIdentityMapperFactory;
use Netric\Entity\Notifier\NotifierFactory;
use Netric\EntitySync\Commit\CommitManagerFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\Entity\Validator\EntityValidatorFactory;
use Netric\EntityDefinition\EntityDefinition;
use Netric\EntityGroupings\GroupingLoaderFactory;
use Netric\EntityDefinition\Field;
use Netric\Account\Account;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\ObjectTypes;
use Ramsey\Uuid\Uuid;

/**
 * A DataMapper is responsible for writing and reading data from a persistant store
 */
abstract class DataMapperAbstract extends \Netric\DataMapperAbstract
{
    /**
     * The type of object this data mapper is handling
     *
     * @var string
     */
    protected $objType = "";

    /**
     * Record of moved-to references
     *
     * @var array
     */
    protected $movedToRef = array();

    /**
     * Commit manager used to crate global commits for sync
     *
     * @var CommitManager
     */
    protected $commitManager = null;

    /**
     * Recurrence Identity Mapper
     *
     * @var RecurrenceIdentityMapper
     */
    private $recurIdentityMapper = null;

    /**
     * Caches the results on checking if entity has moved
     *
     * @var array
     */
    private $cacheMovedEntities = null;

    /**
     * Class constructor
     *
     * @param ServiceLocator $sl The ServiceLocator container
     * @param string $accountName The name of the ANT account that owns this data
     */
    public function __construct(Account $account)
    {
        $this->setAccount($account);
        $this->setUp();

        // Clear the moved entities cache
        $this->cacheMovedEntities = array();

        $serviceManager = $account->getServiceManager();
        $this->recurIdentityMapper = $serviceManager->get(RecurrenceIdentityMapperFactory::class);
        $this->commitManager = $serviceManager->get(CommitManagerFactory::class);
        $this->entitySync = $serviceManager->get(EntitySyncFactory::class);
    }

    /**
     * Set this object as having been moved to another object
     *
     * @param EntityDefinition $def The defintion of this object type
     * @param string $fromId The id to move
     * @param stirng $toId The unique id of the object this was moved to
     * @return bool true on succes, false on failure
     */
    abstract public function setEntityMovedTo(EntityDefinition $def, $fromId, $toId);

    /**
     * Update the old references when moving an entity
     *
     * @param EntityDefinition $def The defintion of this object type
     * @param string $fromId The id to move
     * @param stirng $toId The unique id of the object this was moved to
     */
    abstract public function updateOldReferences(EntityDefinition $def, $fromId, $toId);

    /**
     * The setup function is used by all derrived classes as constructors
     */
    abstract protected function setUp();

    /**
     * Open object by id
     *
     * @var Entity $entity The entity to load data into
     * @var string $id The Id of the object
     * @return bool true on success, false on failure
     */
    abstract protected function fetchById($entity, $id);

    /**
     * Get entity data by guid
     *
     * @param string $guid
     * @return array|null
     */
    abstract protected function fetchDataByGuid(string $guid):? array;

    /**
     * Purge data from the database
     *
     * @var Entity $entity The entity to load data into
     * @return bool true on success, false on failure
     */
    abstract protected function deleteHard($entity);

    /**
     * Flag data as deleted or archive but don't actually delete it
     *
     * @var Entity $entity The entity to load data into
     * @return bool true on success, false on failure
     */
    abstract protected function deleteSoft($entity);

    /**
     * Save object data
     *
     * @param Entity $entity The entity to save
     * @return string|bool entity id on success, false on failure
     */
    abstract protected function saveData($entity);

    /**
     * Check if an object has moved
     *
     * @param EntityDefinition $def The defintion of this object type
     * @param string $id The id of the object that no longer exists - may have moved
     * @return string|bool New entity id if moved, otherwise false
     */
    abstract protected function entityHasMoved($def, $id);

    /**
     * Save revision snapshot
     *
     * @param Entity $entity The entity to save
     * @return string|bool entity id on success, false on failure
     */
    abstract protected function saveRevision($entity);

    /**
     * Get Revisions for this object
     *
     * @param string $objType The name of the object type to get
     * @param string $id The unique id of the object to get revisions for
     * @return array("revisionNum"=>Entity)
     */
    abstract public function getRevisions($objType, $id);

    /**
     * Save main processor
     *
     * @param Entity $entity The enitity to save
     * @param UserEntity $user Optional user performing the save if other than current in $this->account
     * @return int|bool If success the id of the saved entity will be returned, false if failure
     */
    public function save($entity, $user = null)
    {
        $serviceManager = $this->getAccount()->getServiceManager();
        $def = $entity->getDefinition();

        // First validate that this entity is ok to be written
        $entityValidator = $serviceManager->get(EntityValidatorFactory::class);
        if (!$entityValidator->isValid($entity, $this)) {
            $this->errors = array_merge($this->errors, $entityValidator->getErrors());
            return false;
        }

        // Increment revision for this save
        $revision = $entity->getValue("revision");
        $revision = (!$revision) ? 1 : ++$revision;
        $entity->setValue("revision", $revision);

        // Create new global commit revision
        $lastCommitId = $entity->getValue('commit_id');
        $commitId = $this->commitManager->createCommit("entities/" . $def->getObjType());
        $entity->setValue('commit_id', $commitId);

        // If user is not set, then we need to get the current logged in user
        if (!$user) {
            $user = $this->getAccount()->getUser();
        }

        // Set defaults including ts_updated
        $event = ($entity->getId()) ? "update" : "create";
        $entity->setFieldsDefault($event, $user);

        // Create a unique name if the entity supports it
        $this->setUniqueName($entity);

        // Create global uuid if not already set
        $this->setGlobalId($entity);

        // Update foreign key names
        $this->updateForeignKeyNames($entity);
        
        /*
         * If the entity has a new recurrence pattern, then we need to get the next recurring id
         * now so we can save it to the entity before saving the recurring patterns itself.
         * This is the result of a circular reference where the recurrence pattern has a
         * reference to the first entity id, and the entity has a reference to the recurrence
         * pattern. We might want to come up with a better overall solution. - Sky Stebnicki
         */
        $useRecurId = null;
        if ($entity->getRecurrencePattern() && $def->recurRules) {
            if (!$entity->getValue($def->recurRules['field_recur_id'])) {
                $useRecurId = $this->recurIdentityMapper->getNextId();
                $entity->getRecurrencePattern()->setId($useRecurId);
                $entity->setValue($def->recurRules['field_recur_id'], $useRecurId);
            }
        }

        // Call beforeSave
        $entity->beforeSave($serviceManager);

        // Save data to DataMapper implementation
        $ret = $this->saveData($entity);

        // Save revision for historical reference
        if ($def->storeRevisions) {
            $this->saveRevision($entity);
        }

        // Save data to EntityQuery Index
        $serviceManager->get(IndexFactory::class)->save($entity);

        // Clear cache in the EntityLoader
        $serviceManager->get(EntityLoaderFactory::class)->clearCache($def->getObjType(), $entity->getId());

        // Log the change in entity sync
        if ($ret && $lastCommitId && $commitId) {
            $this->entitySync->setExportedStale(
                EntitySync::COLL_TYPE_ENTITY,
                $lastCommitId,
                $commitId
            );
        }

        // Send notifications
        $serviceManager->get(NotifierFactory::class)->send($entity, $event);

        // Call onAfterSave
        $entity->afterSave($serviceManager);

        // Update any aggregates that could be impacted by saving $entity
        $this->getAccount()
            ->getServiceManager()
            ->get(EntityAggregatorFactory::class)
            ->updateAggregates($entity);

        // Reset dirty flag and changelog
        $entity->resetIsDirty();

        /*
         * If this is part of a recurring series - which means it has a recurrence pattern -
         * and not an exception, then save the recurrence pattern.
         */
        if (!$entity->isRecurrenceException() && $entity->getRecurrencePattern()) {
            $this->recurIdentityMapper->saveFromEntity($entity, $useRecurId);
        }

        // Log the activity
        $serviceManager->get(ActivityLogFactory::class)->log($user, $event, $entity);

        return $ret;
    }

    /**
     * Get an entity by id
     *
     * @param EntityInterface $entity The enitity to save
     * @param int $id The unique id of the entity to load
     * @return bool true if found and loaded successfully, false if not found or failed
     */
    public function getById(EntityInterface $entity, $id)
    {
        if (!empty($id) && !is_numeric($id)) {
            throw new \InvalidArgumentException("$id is not a valid entity id");
        }

        $ret = $this->fetchById($entity, $id);

        if (!$ret) {
            $movedToId = $this->entityHasMoved($entity->getDefinition(), $id);
            if ($movedToId && $movedToId != $id) {
                $ret = $this->fetchById($entity, $movedToId);
            }
        }

        // Load a recurrence pattern if set
        if ($entity->getDefinition()->recurRules) {
            // If we have a recurrence pattern id then load it
            $recurId = $entity->getValue($entity->getDefinition()->recurRules['field_recur_id']);
            if ($recurId) {
                $recurPattern = $this->recurIdentityMapper->getById($recurId);
                if ($recurPattern) {
                    $entity->setRecurrencePattern($recurPattern);
                }
            }
        }

        // Reset dirty flag and changelog since we just loaded
        $entity->resetIsDirty();

        return $ret;
    }

    /**
     * Load an entity by a universally unique global id
     *
     * @param string $guid
     * @return EntityInterface|null
     */
    public function getByGuid(string $guid): ? EntityInterface
    {
        $serviceManager = $this->getAccount()->getServiceManager();
        $entityFactory = $serviceManager->get(EntityFactoryFactory::class);
        $data = $this->fetchDataByGuid($guid);

        if (!$data || empty($data['obj_type'])) {
            return null;
        }

        $entity = $entityFactory->create($data['obj_type']);
        $entity->fromArray($data);
        return $entity;
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
        $serviceManager = $this->getAccount()->getServiceManager();
        $definitionLoader = $serviceManager->get(EntityDefinitionLoaderFactory::class);
        $entityFactory = $serviceManager->get(EntityFactoryFactory::class);
        $def = $definitionLoader->get($objType);

        // Sanitize in case the user passed in bad paths like '/my//path'
        $uniqueNamePath = str_replace("//", "/", $uniqueNamePath);

        // Remove a trailing '/'
        if ($uniqueNamePath[strlen($uniqueNamePath) - 1] === '/') {
            $uniqueNamePath = substr($uniqueNamePath, 0, -1);
        }

        // Remove a root '/'
        if ($uniqueNamePath[0] === '/') {
            $uniqueNamePath = substr($uniqueNamePath, 1);
        }

        // Now split the full sanitized path into segments
        $segments = explode("/", $uniqueNamePath);

        // Pop the uname of the current level off the path
        $uname = array_pop($segments);

        // Check if this object has a parent field and there are more unames upstream in the path
        $parentFieldCondition = [];
        if ($def->parentField && count($segments) >= 1) {
            $parentField = $def->getField($def->parentField);
            if ($parentField->type === "object" && !empty($parentField->subtype)) {
                $parentEntity = $this->getByUniqueName(
                    $parentField->subtype,
                    implode('/', $segments),
                    $namespaceFieldValues
                );

                // If we can't find the parent then the path does not exist
                if (!$parentEntity) {
                    return null;
                }

                $parentFieldCondition[$def->parentField] = $parentEntity->getGuid();
            }
        }

        $filterValues = array_merge($namespaceFieldValues, $parentFieldCondition, ['uname' => $uname]);
        $matches = $this->getIdsFromFieldValues($objType, $filterValues);
        if (count($matches) == 1) {
            $entity = $entityFactory->create($objType);
            $this->getById($entity, $matches[0]);
            return $entity;
        }

        // Could not find a unique match
        return null;
    }

    /**
     * Look for IDs based on field values
     *
     * @param string $objType The type of entity we are querying
     * @param array $conditionValues Array of field values to query for
     * @return string[] Array of IDs that match the field values
     */
    private function getIdsFromFieldValues($objType, array $conditionValues)
    {
        $entityIds = [];

        // Search objects to see if the uname exists
        $query = new EntityQuery($objType);

        foreach ($conditionValues as $fieldName => $fieldCondValue) {
            $query->andWhere($fieldName)->equals($fieldCondValue);
        }

        // Query for matching IDs
        $serviceManager = $this->getAccount()->getServiceManager();
        $index = $serviceManager->get(IndexFactory::class);
        $result = $index->executeQuery($query);
        for ($i = 0; $i < $result->getTotalNum(); $i++) {
            $entity = $result->getEntity($i);
            $entityIds[] = $entity->getId();
        }

        return $entityIds;
    }

    /**
     * Delete an entity
     *
     * @param Entity $entity The enitity to save
     * @param bool $forceHard If true the data will be purged, if false first it will be archived
     * @return bool true on success, false on failure
     */
    public function delete($entity, $forceHard = false)
    {
        $user = $this->getAccount()->getUser();
        $serviceManager = $this->getAccount()->getServiceManager();

        $lastCommitId = $entity->getValue("commit_id");
        // Create new global commit revision
        $commitId = $this->commitManager->createCommit("entities/" . $entity->getDefinition()->getObjType());

        // Determine if we are flagging the entity as deleted or actually purging
        if ($entity->getValue("f_deleted") || $forceHard) {
            // Call beforeDeleteHard so the entity can do any pre-purge operations
            $entity->beforeDeleteHard($serviceManager);

            // Purge the recurrence pattern if set
            if ($entity->getRecurrencePattern()) {
                // Only delete the recurrence pattern if this is the original
                if ($entity->getRecurrencePattern()->entityIsFirst($entity)) {
                    $this->recurIdentityMapper->delete($entity->getRecurrencePattern());
                }
            }

            // Perform the delete from the data store
            $ret = $this->deleteHard($entity);

            // Call onBeforeDeleteHard so the entity can do any post-purge operations
            $entity->afterDeleteHard($serviceManager);

            // Delete from EntityCollection_Index
            $serviceManager->get(IndexFactory::class)->save($entity);
        } else {
            $entity->setValue('commit_id', $commitId);

            $ret = null;
            try {
                $ret = $this->deleteSoft($entity);
            } catch (DefinitionStaleException $ex) {
                /*
                 * We tried to save but there was something wrong with the definition (field not added?)
                 * Sometimes we need to force the system fields to reset in order to update
                 * the entity database -- especially if a new field was added to system fields.
                 */

                // Try to update the definition in case it is out of sync
                $entityDefLoader = $serviceManager->get(EntityDefinitionLoaderFactory::class);
                $entityDefLoader->forceSystemReset($entity->getDefinition()->getObjType());

                // Try deleting again
                $ret = $this->deleteSoft($entity);
            }

            // Delete from EntityCollection_Index
            //$this->getServiceLocator()->get("EntityCollection_Index")->delete($entity);

            // Log the activity
            $alog = $serviceManager->get(ActivityLogFactory::class);
            $alog->log($user, "delete", $entity);
        }

        // Log the change in entity sync
        if ($ret && $lastCommitId && $commitId) {
            $this->entitySync->setExportedStale(
                \Netric\EntitySync\EntitySync::COLL_TYPE_ENTITY,
                $lastCommitId,
                $commitId
            );
        }

        // Clear cache in the EntityLoader
        $serviceManager->get(EntityLoaderFactory::class)->clearCache($entity->getDefinition()->getObjType(), $entity->getId());

        return $ret;
    }

    /**
     * Update foreign key name cache
     *
     * All foreign key (fkey, fkey_multi, object, object_multi) fields
     * cache the name of the foreign key for faster performance. The risk
     * with this is that the cache gets out of date if a referenced object
     * is updated. This function makes sure that all names for foreign references
     * are refreshed any time the entity is saved.
     *
     * @param Entity $entity The entity to update
     */
    private function updateForeignKeyNames(Entity $entity)
    {
        $serviceManager = $this->getAccount()->getServiceManager();
        $groupingsLoader = $serviceManager->get(GroupingLoaderFactory::class);
        $entityLoader = $serviceManager->get(EntityLoaderFactory::class);

        // Make sure that private groupings always have user_guid set
        $userGuidPath = "";
        if ($entity->getDefinition()->isPrivate()) {
            // All entities have owner_id, but some old entities use user_id
            $userId = $entity->getValue("owner_id") !== null ? $entity->getValue("owner_id") : $entity->getValue("user_id");
            $userEntity = $entityLoader->get(ObjectTypes::USER, $userId);
            
            if ($userEntity) {
                $userGuidPath = "/" . $userEntity->getGuid();
            } else {
                // If we do not find the owner_id or user_id, then let's use the current user id.
                $userGuidPath = "/" . $this->getAccount()->getUser()->getGuid();
            }
        }


        $fields = $entity->getDefinition()->getFields();
        foreach ($fields as $field) {
            $value = $entity->getValue($field->name);

            // Skip over null/empty fields
            if (!$value) {
                continue;
            }

            switch ($field->type) {
                case Field::TYPE_OBJECT:
                    // Get the referenced entity
                    $referencedEntity = $entityLoader->getByGuidOrObjRef($value, $field->subtype);

                    // If we havent found the referenced entity, chances are it was already removed, so we need to clear the value
                    if (!$referencedEntity) {
                        $entity->setValue($field->name, null);
                        continue;
                    }

                    // Since we have found the referenced entity, then add it in the entity
                    $entity->setValue($field->name, $referencedEntity->getGuid(), $referencedEntity->getName());
                    break;

                case Field::TYPE_OBJECT_MULTI:

                    if (is_array($value)) {
                        foreach ($value as $id) {
                            // Get the referenced entity
                            $referencedEntity = $entityLoader->getByGuidOrObjRef($id, $field->subtype);

                            // If we havent found the referenced entity, chances are it was already removed, so we need to clear the value
                            if (!$referencedEntity) {
                                $entity->removeMultiValue($field->name, $id);    
                                continue;
                            }

                            // Since we have found the referenced entity, then add it in the entity
                            $entity->addMultiValue($field->name, $referencedEntity->getGuid(), $referencedEntity->getName());
                        }
                    }

                    break;

                case Field::TYPE_GROUPING:
                    $objType = $entity->getDefinition()->getObjType();
                    $grouping = $groupingsLoader->get("$objType/{$field->name}$userGuidPath");

                    // Clear the value in preparation for an update - or to remove it if group was deleted
                    $entity->setValue($field->name, null);
                    $group = $grouping->getByGuidOrGroupId($value);
                    if ($group) {
                        // If the group exists then update the name
                        $entity->setValue($field->name, $value, $group->name);
                    }
                    break;

                case Field::TYPE_GROUPING_MULTI:
                    $objType = $entity->getDefinition()->getObjType();
                    $grouping = $groupingsLoader->get("$objType/{$field->name}$userGuidPath");
                    
                    if (is_array($value)) {
                        foreach ($value as $id) {
                            // Clear the value in preparation for an update - or to remove it if group was deleted
                            $entity->removeMultiValue($field->name, $id);
                            $group = $grouping->getByGuidOrGroupId($id);
                            if ($group) {
                                $entity->addMultiValue($field->name, $group->guid, $group->name);
                            }
                        }
                    }

                    break;
            }
        }
    }

    /**
     * When saving an entity create a unqiue name if not already set
     *
     * @param EntityInterface $entity
     * @return bool true if changed, false if failed
     */
    private function setUniqueName(EntityInterface $entity)
    {
        $def = $entity->getDefinition();

        // If we are not using unique names with this object just return
        if (!$def->unameSettings) {
            return false;
        }

        // If we have already created a uname and saved it then do nothing
        if ($entity->getValue("uname")) {
            return false;
        }

        $unameSettings = explode(":", $def->unameSettings);

        // Create desired uname from the right field
        // Format is: "<opt_namespaced_field>:<field_to_get_unique_name_from>""
        $lastPart = end($unameSettings);

        // The unique name field is the last part of unameSettings
        $uname = ($lastPart == "name") ? $entity->getName() : $entity->getValue($lastPart);

        // The uname must be populated before we try to save anything
        if (!$uname) {
            return;
        }

        // Now escape the uname field to a uri friendly name
        $uname = strtolower($uname);
        $uname = str_replace(" ", "-", $uname);
        $uname = str_replace("&", "_and_", $uname);
        $uname = str_replace("@", "_at_", $uname);
        $uname = preg_replace('/[^A-Za-z0-9._-]/', '', $uname);

        $isUnique = $this->verifyUniqueName($entity, $uname);

        // If the unique name already exists, then append with id or a random number
        if (!$isUnique) {
            $uname .= "-";
            $uname .= ($this->id) ? $this->id : uniqid();
        }

        // Set the uname
        $entity->setValue("uname", $uname);
        return true;
    }

    /**
     * Make sure that a uname is still 7unique
     *
     * This should safe-gard against values being saved in the object that change the namespace
     * of the unique name causing unique collision.
     *
     * @param Entity $entity The entity to save
     * @param string $uname The name to test for uniqueness
     * @return bool true if the uniqueName is truly unique or false if there is a collision
     */
    public function verifyUniqueName($entity, $uname)
    {
        $serviceManager = $this->getAccount()->getServiceManager();
        $def = $entity->getDefinition();

        // If we are not using unique names with this object just succeed
        if (!$def->unameSettings) {
            return true;
        }

        // Search objects to see if the uname exists
        $query = new EntityQuery($def->getObjType());
        $query->where("uname")->equals($uname);

        // Exclude this object from the query because of course it will be a duplicate
        if ($entity->getId()) {
            $query->andWhere("id")->doesNotEqual($entity->getId());
        }

        /*
         * Loop through all namespaces if set with ':' in the settings
         * The first part of the settings is "<opt_namespace_field>:"
         * and it can have as many namespaces as needed with the last entry
         * being the entity field that is used to generate the unique name.
         */
        $nsParts = explode(":", $def->unameSettings);
        if (count($nsParts) > 1) {
            // Use all but last, which is the uname field
            for ($i = 0; $i < (count($nsParts) - 1); $i++) {
                $query->andWhere($nsParts[$i])->equals($entity->getValue($nsParts[$i]));
            }
        }

        // Check if any objects match
        $index = $serviceManager->get(IndexFactory::class);
        $result = $index->executeQuery($query);

        if ($result->getTotalNum() > 0) {
            return false;
        }

        // Name is unique
        return true;
    }

    /**
     * Make sure the entity has a global unique id (create it if not)
     *
     * @param EntityInterface $entity
     */
    public function setGlobalId(EntityInterface $entity)
    {
        if (!$entity->getValue('guid')) {
            $uuid4 = Uuid::uuid4();
            $entity->setValue('guid', $uuid4->toString());
        }
    }

    /**
     * Check if an object has moved
     *
     * @param EntityDefinition $def The defintion of this object type
     * @param string $localId The id of the object that no longer exists - may have moved
     * @return string|bool New entity id if moved, otherwise false
     */
    public function checkEntityHasMoved($def, $localId)
    {
        $cachedId = $def->getObjType() . "-" . $localId;
        /*
         * If we have already checked this entity, then return the result
         * If the cached result is empty, then will try to check again if the entity has been moved now
         */
        if (isset($this->cacheMovedEntities[$cachedId])) {
            return $this->cacheMovedEntities[$cachedId];
        }

        // Check if entity has moved
        $movedToId = $this->entityHasMoved($def, $localId);

        // Store the result in the cache
        if ($movedToId) {
            $this->cacheMovedEntities[$cachedId] = $movedToId;
        }

        return $movedToId;
    }
}
