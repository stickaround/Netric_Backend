<?php

namespace Netric\Entity\DataMapper;

use DateTime;
use Netric\Db\Relational\Exception\DatabaseQueryException;
use Netric\Entity\DataMapperAbstract;
use Netric\Entity\DataMapperInterface;
use Netric\Db\Relational\RelationalDbInterface;
use Netric\Entity\EntityInterface;
use Netric\EntityDefinition\Field;
use Netric\Entity\EntityFactoryFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\EntityDefinition\EntityDefinition;
use Netric\Db\Relational\RelationalDbFactory;
use Netric\Entity\Entity;
use Netric\EntityQuery;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\EntityGroupings\GroupingLoaderFactory;
use Ramsey\Uuid\Uuid;

/**
 * Load and save entity data to a relational database
 */
class EntityRdbDataMapper extends DataMapperAbstract implements DataMapperInterface
{
    /**
     * Name of the tables where entity data is saved
     */
    const ENTITY_TABLE = 'entity';
    const ENTITY_REVISION_TABLE = 'entity_revision';
    const ENTITY_MOVED_TABLE = 'entity_moved';

    /**
     * Handle to database
     *
     * @var RelationalDbInterface
     */
    private $database = null;

    /**
     * Setup this class called from the parent constructor
     */
    protected function setUp()
    {
        $this->database = $this->account->getServiceManager()->get(RelationalDbFactory::class);
    }

    /**
     * Open object by id
     *
     * @var EntityInterface $entity The entity to load data into
     * @var string $id The Id of the object
     * @return bool true on success, false on failure
     */
    protected function fetchById($entity, $entityId, $skipObjRefUpdate = false)
    {
        $sql = 'SELECT guid, object_type_id, field_data FROM ' . self::ENTITY_TABLE . ' WHERE guid=:id';
        $result = $this->database->query($sql, ['guid' => $entityId]);

        // The entity was not found
        if ($result->rowCount() === 0) {
            return false;
        }

        // Load rows and set values in the entity
        $row = $result->fetch();
        $entityData = json_decode($row['field_data'], true);

        $def = $entity->getDefinition();
        $allFields = $def->getFields();
        foreach ($allFields as $field) {
            // Sanitize the entity value.
            $value = $this->sanitizeDbValuesToEntityFieldValue($field, $entityData[$field->name]);

            $valueName = null;
            if (!empty($entityData["{$field->name}_fval"])) {
                $valueName = $entityData["{$field->name}_fval"];
            }

            // Set entity value
            $entity->setValue($field->name, $value, $valueName);
        }

        // Make sure that we are now using guid for object references
        //        if (!$skipObjRefUpdate) {
        //            $this->updatObjectReferencesToGuid($entity);
        //        }

        return true;
    }

    /**
     * Update the object references to guid instead of just an id.
     *
     * @param EntityInterface $entity The entity to update its object references
     */
    private function updatObjectReferencesToGuid(Entity $entity)
    {
        $entityLoader = $this->getAccount()->getServiceManager()->get(EntityLoaderFactory::class);
        $groupingLoader = $this->account->getServiceManager()->get(GroupingLoaderFactory::class);

        $entity->resetIsDirty();
        $fields = $entity->getDefinition()->getFields();
        foreach ($fields as $field) {

            switch ($field->type) {
                case Field::TYPE_GROUPING:
                case Field::TYPE_GROUPING_MULTI:
                    $fieldValue = $entity->getValue($field->name);
                    $ownerGuid = $entity->getOwnerGuid();

                    // Since we do not know if the group saved is a private grouping, we will just query both groupings and look for its id
                    $publicGroupings = $groupingLoader->get($entity->getObjType() . "/{$field->name}");

                    // Only load the private groupings if we have a valid ownerGuid
                    if ($ownerGuid) {
                        $privateGroupings = $groupingLoader->get($entity->getObjType() . "/{$field->name}/$ownerGuid");
                    }

                    // Check if this field is a grouping multi
                    if ($field->isMultiValue()) {

                        // Make sure that we have fieldValue and it is an array
                        if ($fieldValue && is_array($fieldValue)) {

                            // Loop thru the fieldValue and look for referenced group that still have id
                            foreach ($fieldValue as $value) {

                                if (!$value) {
                                    continue;
                                }

                                // Look first in public groupings and see if the group id exists.
                                $group = $publicGroupings->getByGuidOrGroupId($value);

                                // If we haven't found the group in the public groupings, then let's look in the private groupings
                                if (!$group && $privateGroupings) {
                                    $group = $privateGroupings->getByGuidOrGroupId($value);
                                }

                                // Make sure that we have retrieved now the group from private groupings or public groupings
                                if ($group) {
                                    // Before adding the new guid value of the group, we need to remove first the existing one.
                                    $entity->removeMultiValue($field->name, $value);

                                    // Now that we have already removed the old group id, we can now add the new group's guid
                                    $entity->addMultiValue($field->name, $group->guid, $group->name);
                                }
                            }
                        }
                    } else if ($fieldValue && !Uuid::isValid($fieldValue) && $fieldValue) {
                        // Here we will handle the grouping field and make sure that the fieldValue is still not a guid

                        // Look first in public groupings and see if the group id exists.
                        $group = $publicGroupings->getByGuidOrGroupId($fieldValue);

                        // If we haven't found the group in the public groupings, then let's look in the private groupings
                        if (!$group && $privateGroupings) {
                            $group = $privateGroupings->getByGuidOrGroupId($fieldValue);
                        }

                        // Make sure that we have retrieved the group
                        if ($group) {
                            $entity->setValue($field->name, $group->guid, $group->name);
                        }
                    }
                    break;

                case Field::TYPE_OBJECT:
                    $objValue = $entity->getValue($field->name);

                    if ($objValue) {
                        // Get the referenced entity
                        $referencedEntity = $entityLoader->getByGuid($objValue);

                        if ($referencedEntity) {
                            $entity->setValue($field->name, $referencedEntity->getEntityId(), $referencedEntity->getName());
                        }
                    }
                    break;

                case Field::TYPE_OBJECT_MULTI:
                    $refValues = $entity->getValue($field->name);

                    // Make sure the the multi value is an array
                    if (is_array($refValues)) {
                        foreach ($refValues as $value) {

                            if ($value) {
                                // Get the referenced entity
                                $referencedEntity = $entityLoader->getByGuid($value);

                                // If we have successfully loaded the referenced entity, then we will add its guid
                                if ($referencedEntity) {
                                    // Before adding the new guid value of the object, we need to remove first the existing one.
                                    $entity->removeMultiValue($field->name, $value);

                                    // Now that we have already removed the old object id, we can now add the new object's guid
                                    $entity->addMultiValue($field->name, $referencedEntity->getEntityId(), $referencedEntity->getName());
                                }
                            }
                        }
                    }
                    break;
            }
        }

        // Save this entity only if there were changes made.
        if ($entity->isDirty()) {
            $this->saveData($entity);
        }
    }

    /**
     * Get entity data by guid
     *
     * @param string $guid
     * @return array|null
     */
    protected function fetchDataByGuid(string $guid): ?array
    {
        $sql = 'SELECT guid, field_data FROM ' . self::ENTITY_TABLE . ' where guid = :guid';
        $result = $this->database->query($sql, ['guid' => $guid]);

        // The object was not found
        if ($result->rowCount() === 0) {
            return null;
        }

        // Load rows and set values in the entity
        $row = $result->fetch();
        $entityData = json_decode($row['field_data'], true);

        /**
         * Override any of the json data with system column values
         * Some of these may be generated at update/insert so they could have
         * changed after the entity was exported and saved to the column
         */
        $entityData['guid'] = $row['guid'];
        return $entityData;
    }

    /**
     * Handle any conversions from database values to entity values
     *
     * Example of this would be when the database returns a bool, it will be
     * a character 'f' for false or 't' for true. We need to convert that to
     * boolean true or false types for the entity.
     *
     * @param Field $field
     * @param [type] $databaseValue
     * @return mixed
     */
    public function sanitizeDbValuesToEntityFieldValue(Field $field, $databaseValue)
    {
        switch ($field->type) {
            case Field::TYPE_BOOL:
                return ($databaseValue == 't') ? true : false;
            case Field::TYPE_DATE:
            case Field::TYPE_TIMESTAMP:
                return ($databaseValue) ? strtotime($databaseValue) : null;
            case Field::TYPE_OBJECT_MULTI:
                /*
                 * Make sure the id is an actual number
                 * We have to do this because some old entities
                 * have bad values in object_multi fields
                 */
                if ($field->subtype && is_array($databaseValue)) {
                    foreach ($databaseValue as $index => $id) {
                        if (is_numeric($id)) {
                            $databaseValue[$index] = $id;
                        }
                    }
                }

                return $databaseValue;
            default:
                return $databaseValue;
        }
    }

    /**
     * Delete object by id
     *
     * @var Entity $entity The entity to load data into
     * @return bool true on success, false on failure
     */
    protected function deleteHard($entity)
    {
        // Only delete existing objects
        if (!$entity->getEntityId()) {
            return false;
        }

        $def = $entity->getDefinition();

        // Remove revision history
        $this->database->query(
            'DELETE FROM ' . self::ENTITY_REVISION_TABLE . ' WHERE entity_id=:entity_id',
            ['entity_id' => $entity->getEntityId()]
        );

        // Delete the object from the object table
        // TODO: Change guid to entity_id
        $sql = "DELETE FROM " . self::ENTITY_TABLE . " WHERE guid=:id";
        $result = $this->database->query($sql, ['id' => $entity->getEntityId()]);

        // We just need to make sure the main object was deleted
        return ($result->rowCount() > 0);
    }

    /**
     * Delete object by id
     *
     * @var Entity $entity The entity to load data into
     * @return bool true on success, false on failure
     */
    protected function deleteSoft($entity)
    {
        // Update the deleted flag and save
        $entity->setValue("f_deleted", true);
        $ret = $this->save($entity);
        return ($ret === false) ? false : true;
    }

    /**
     * Save object data
     *
     * @param Entity $entity The entity to save
     * @return string|bool entity id on success, false on failure
     * @throws \RuntimeException If there is a problem saving to the database
     */
    protected function saveData($entity)
    {
        $def = $entity->getDefinition();

        // Convert to cols=>vals array
        $data = $this->getDataToInsertFromEntity($entity);

        $all_fields = $def->getFields();

        // Set typei_id to correctly build the sql statement based on custom table definitions
        $data["object_type_id"] = $def->getEntityDefinitionId();

        // Set data as JSON (we are replacing columns with this for custom fields)
        $data['field_data'] = json_encode($entity->toArray());

        $targetTable = self::ENTITY_TABLE;


        if ($entity->getValue("revision") > 1) {
            $this->updateEntityData($targetTable, $entity);
        } else {
            $this->database->insert($targetTable, $data);
        }

        return $entity->getEntityId();
    }

    /**
     * Update the entity data
     * 
     * @param $targetTable Table that we will be using to update the entity (deleted or active partition)
     * @param $entity The entity that will be updated
     */
    private function updateEntityData(string $targetTable, Entity $entity)
    {
        $sql = "UPDATE $targetTable SET field_data = :field_data, f_deleted = :f_deleted WHERE guid = :guid";
        $this->database->query($sql, [
            "field_data" => json_encode($entity->toArray()),
            "f_deleted" => $entity->getValue('f_deleted'),
            "guid" => $entity->getValue('guid')
        ]);
    }

    /**
     * Convert fields to column names for saving table and escape for insertion/updates
     *
     * @param EntityInterface $entity The entity we are saving
     * @return array("col_name"=>"value")
     */
    private function getDataToInsertFromEntity(EntityInterface $entity)
    {
        $ret = [];
        $all_fields = $entity->getDefinition()->getFields();

        foreach ($all_fields as $fname => $fdef) {
            /*
             * Check if the field name does exists in the object table
             * Most of the entity data are already stored in field_data column
             * So there is no need to build a data array for entity values
             */
            if (!$this->database->columnExists(self::ENTITY_TABLE, $fname)) {
                continue;
            }

            $val = $entity->getValue($fname);

            // Skip over an empty id field - we won't want to try and set it
            if ($fname === 'id' && empty($val)) {
                continue;
            }

            switch ($fdef->type) {
                case 'auto':
                    // Calculated fields should not be set from entity
                    break;
                case 'fkey_multi':
                case 'object_multi':
                    $ret[$fname] = json_encode(($val) ? $val : []);
                    break;
                case 'int':
                case 'integer':
                case 'double':
                case 'double precision':
                case 'float':
                case 'real':
                case 'number':
                case 'numeric':
                    if (is_numeric($val)) {
                        if ($fdef->subtype == "integer" && $val) {
                            $ret[$fname] = (int) $val;
                        } else {
                            $ret[$fname] = $val;
                        }
                    } else {
                        $ret[$fname] = null;
                    }

                    break;
                case 'date':
                    // All date fields are epoch timestamps
                    if (is_numeric($val) && $val > 0) {
                        $ret[$fname] = date("Y-m-d", $val);
                    } else {
                        $ret[$fname] = null;
                    }
                    break;
                case 'timestamp':
                    // All timestamp fields are epoch timestamps
                    if (is_numeric($val) && $val > 0) {
                        $ret[$fname] = date(DateTime::ATOM, $val);
                    } else {
                        $ret[$fname] = null;
                    }
                    break;
                case 'text':
                    $tmpval = $val;
                    // Check if the field has a limited length
                    if (is_numeric($fdef->subtype)) {
                        if (strlen($tmpval) > $fdef->subtype) {
                            $tmpval = substr($tmpval, 0, $fdef->subtype);
                        }
                    }
                    $ret[$fname] = $tmpval;
                    break;
                case 'bool':
                    $ret[$fname] = ($val === true);
                    break;
                case 'fkey':
                    // If val is already guid, then this value is already saved in field_data
                    if (Uuid::isValid($val)) {
                        continue;
                    }

                    $ret[$fname] = $val ? $val : null;
                    break;
                case 'object':
                    // If val is already guid, then this value is already saved in field_data
                    if (Uuid::isValid($val)) {
                        continue;
                    }

                    $ret[$fname] = $val ? $val : null;
                    break;
                default:
                    $ret[$fname] = $val;
                    break;
            }

            // Set fval cache so we do not have to do crazy joins across tables
            if (
                $fdef->type == "fkey" || $fdef->type == "fkey_multi" ||
                $fdef->type == "object" || $fdef->type == "object_multi"
            ) {
                // Get the value names (if set) and save
                $fvals = $entity->getValueNames($fname);
                if (!is_array($fvals)) {
                    $fvals = [];
                }

                $ret[$fname . "_fval"] = json_encode($fvals);
            }
        }

        return $ret;
    }

    /**
     * Serialize data from an array to a string
     *
     * @param array $data
     * @return string
     */
    private function serialize(array $data)
    {
        return json_encode($data);
    }

    /**
     * Check to see if this object id was moved or merged into a different id
     *
     * @param EntityDefinition $def The defintion of this object type
     * @param string $id The id of the object that no longer exists - may have moved
     * @return string new Entity id if moved, otherwise false
     */
    protected function entityHasMoved($def, string $guid)
    {
        $sql = 'SELECT new_guid FROM ' . self::ENTITY_MOVED_TABLE . '  WHERE ' .
            'old_guid=:old_guid';
        $result = $this->database->query($sql, [
            'old_guid' => $guid
        ]);
        if ($result->rowCount() > 0) {
            $row = $result->fetch();
            return $row['new_guid'];
        }

        return false;
    }

    /**
     * Set this object as having been moved to another object
     *
     * @param string $fromId The id to move
     * @param string $toId The unique id of the object this was moved to
     * @return bool true on succes, false on failure
     * @throws DatabaseQueryException if query fails
     */
    public function setEntityMovedTo(EntityDefinition $def, string $fromGuid, string $toGuid)
    {
        if (!$fromGuid || $fromGuid == $toGuid) { // never allow circular reference or blank values
            return false;
        }

        $data = [
            'old_guid' => $fromGuid,
            'new_guid' => $toGuid,
        ];
        $this->database->insert(self::ENTITY_MOVED_TABLE, $data);

        // Update the referenced entities
        $this->updateOldReferences($def, $fromGuid, $toGuid);

        // If it fails an exception will be thrown
        return true;
    }

    /**
     * Update the old references when moving an entity
     *
     * @param EntityDefinition $def The defintion of this object type
     * @param string $fromId The id to move
     * @param stirng $toId The unique id of the object this was moved to
     */
    public function updateOldReferences(EntityDefinition $def, $fromId, $toId)
    {
        $entityDefinitionLoader = $this->account->getServiceManager()->get(EntityDefinitionLoaderFactory::class);
        $entityIndex = $this->account->getServiceManager()->get(IndexFactory::class);
        $entityLoader = $this->getAccount()->getServiceManager()->get(EntityLoaderFactory::class);

        $toEntity = $entityLoader->getByGuid($toId);
        $definitions = $entityDefinitionLoader->getAll();

        // Loop thru all the entity definitions and check if we have fields that needs to update the reference
        foreach ($definitions as $definition) {
            $fields = $definition->getFields();

            foreach ($fields as $field) {
                // Skip over any fields that are not a reference to an object
                if ($field->type != Field::TYPE_OBJECT && $field->type != Field::TYPE_OBJECT_MULTI) {
                    continue;
                }

                // Create an EntityQuery for each object type
                $query = new EntityQuery($definition->getObjType());
                $oldFieldValue = null;
                $newFieldValue = null;

                // Check if field subtype is the same as the $def objtype and if field is not multivalue
                if ($field->subtype == $def->getObjType()) {
                    $oldFieldValue = $fromId;
                    $newFieldValue = $toEntity->getEntityId();
                }



                // Only continue if the field met one of the conditions above
                if (!$oldFieldValue || !$newFieldValue) {
                    continue;
                }

                // Query the index for entities with a matching field
                $query->where($field->name)->equals($oldFieldValue);
                $result = $entityIndex->executeQuery($query);

                if ($result) {
                    $num = $result->getNum();

                    // Update each entity with a field that matched
                    for ($i = 0; $i < $num; $i++) {
                        $entity = $result->getEntity($i);

                        // Check if field is a multi field
                        if ($field->isMultiValue()) {
                            $entity->removeMultiValue($field->name, $oldFieldValue);
                            $entity->addMultiValue($field->name, $newFieldValue);
                        } else {
                            $entity->setValue($field->name, $newFieldValue);
                        }

                        // Save the changes made in the entity
                        $this->save($entity);
                    }
                }
            }
        }
    }

    /**
     * Save revision snapshot
     *
     * @param Entity $entity The entity to save
     * @return string|bool entity id on success, false on failure
     */
    protected function saveRevision($entity)
    {
        $def = $entity->getDefinition();

        if ($entity->getValue("revision") && $entity->getEntityId() && $def->getEntityDefinitionId()) {
            $insertData = [
                'entity_id' => $entity->getEntityId(),
                'revision' => $entity->getValue("revision"),
                'ts_updated' => 'now',
                'field_data' => json_encode($entity->toArray()),
            ];
            $this->database->insert('entity_revision', $insertData);
        }
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
        if (!$objType || !$id) {
            return null;
        }

        $def = $this->getAccount()->getServiceManager()->get(EntityDefinitionLoaderFactory::class)->get($objType);

        if (!$def) {
            return null;
        }

        $ret = [];

        $results = $this->database->query(
            'SELECT entity_revision_id, revision, field_data FROM entity_revision WHERE entity_id=:id',
            ['id' => $id]
        );
        foreach ($results->fetchAll() as $row) {
            $ent = $this->getAccount()->getServiceManager()->get(EntityFactoryFactory::class)->create($objType);
            $ent->fromArray(json_decode($row['field_data'], true));
            $ret[$row['revision']] = $ent;
        }

        return $ret;
    }
}
