<?php
namespace Netric\Entity\DataMapper;

use Netric\Db\Relational\Exception\DatabaseQueryException;
use Netric\Entity\DataMapperAbstract;
use Netric\Entity\DataMapperInterface;
use Netric\Db\Relational\RelationalDbInterface;
use Netric\Entity\EntityInterface;
use Netric\EntityDefinition\Field;
use DateTime;
use Netric\Entity\EntityFactoryFactory;
use Netric\FileSystem\FileSystemFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\EntityDefinition\EntityDefinition;
use Netric\Entity\EntityLoaderFactory;
use Netric\Db\Relational\RelationalDbFactory;
use Netric\Entity\Entity;
use Netric\Config\ConfigFactory;
use Netric\EntityQuery;
use Netric\EntityQuery\Index\IndexFactory;

/**
 * Load and save entity data to a relational database
 */
class EntityRdbDataMapper extends DataMapperAbstract implements DataMapperInterface
{
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
    protected function fetchById($entity, $id)
    {
        $def = $entity->getDefinition();

        $result = $this->database->query(
            'select * from ' . $def->getTable() . ' where id=:id',
            ['id' => $id]
        );

        // The object was not found
        if ($result->rowCount() === 0) {
            return false;
        }

        // Load rows and set values in the entity
        $row = $result->fetch();
        $allFields = $def->getFields();
        foreach ($allFields as $fieldDefinition) {
            $this->setEntityFieldValueFromRow($entity, $fieldDefinition, $row);
        }

        return true;
    }

    /**
     * Set a field in an entity from a raw database row
     *
     * @param EntityInterface $entity
     * @param Field $field
     * @param array $row
     * @return void
     */
    private function setEntityFieldValueFromRow(EntityInterface $entity, Field $field, array $row)
    {
        // Set IDs and names for referenced groupings or objects
        $foreignReferenceValues = null;
        if ($field->isObjectReference() || $field->isGroupingReference()) {
            $foreignReferenceValues = $this->getForeignValuesForReference($entity, $field, $row);
        }

        /*
         * If the field is multi-value, we need to decode the JSON since it will be
         * stored as a string in the database
         */
        if ($field->isMultiValue()) {
            if (!empty($row[$field->name])) {
                $values = $this->unserialize($row[$field->name]);
                if ($values !== false) {
                    $row[$field->name] = $values;
                }
            } elseif ($foreignReferenceValues) {
                // Error, data not set in the column, check if it was set in an referenced field values
                foreach ($foreignReferenceValues as $referencedId => $referencedName) {
                    $row[$field->name][] = $referencedId;
                }
            }
        }

        // Convert values such as 'f' for a bool in the DB to false
        $row[$field->name] = $this->sanitizeDbValuesToEntityFieldValue($field, $row[$field->name]);

        // Set entity value
        $entity->setValue($field->name, $row[$field->name], $foreignReferenceValues);
    }

    /**
     * Get referenced values for an object or grouping field
     *
     * @param EntityInterface $entity
     * @param Field $field
     * @param $row
     * @return array|null
     */
    private function getForeignValuesForReference(EntityInterface $entity, Field $field, $row)
    {
        $def = $entity->getDefinition();

        // All reference fields should store id and name of references in *_fkey row
        $foreignValues = null;
        if (isset($row[$field->name . "_fval"])) {
            $foreignValues = $this->unserialize($row[$field->name . "_fval"]);
        }

        /*
         * Check if the *_fval field was not set last time the entity was saved.
         * This should only occur in old objects saved before we moved to v2
         * on the backend.
         */
        if (!$foreignValues || ($foreignValues == '[]' && $row[$field->name] != '[]' && $row[$field->name] != '')) {
            $foreignValues = $this->getForeignKeyDataFromDb(
                $field,
                $row[$field->name],
                $entity->getId(),
                $def->getId()
            );
        }


        return $foreignValues;
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
    private function sanitizeDbValuesToEntityFieldValue(Field $field, $databaseValue)
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
        if (!$entity->getId()) {
            return false;
        }

        $def = $entity->getDefinition();

        // Remove revision history
        $this->database->query(
            'DELETE FROM object_revisions WHERE object_id=:object_id ' .
                'AND object_type_id=:object_type_id',
            ['object_id' => $entity->getId(), 'object_type_id' => $def->getId()]
        );

        // Delete the object from the object table
        $result = $this->database->query(
            "DELETE FROM " . $def->getTable() . " where id=:id",
            ['id' => $entity->getId()]
        );

        // Remove associations
        $this->deleteAssociations($def->getId(), $entity->getId);

        // We just need to make sure the main object was deleted
        return ($result->rowCount() > 0);
    }

    /**
     * Delete object associations related to an entity
     *
     * @param int $objTypeId
     * @param int $id Entity id
     * @return void
     */
    private function deleteAssociations($objTypeId, $id)
    {
        $this->database->query(
            'DELETE FROM object_associations WHERE ' .
                '(object_id=:object_id and type_id=:type_id) OR ' .
                '(assoc_object_id=:object_id and assoc_type_id=:type_id)',
            [
                'object_id' => $id, 'type_id' => $objTypeId,
            ]
        );
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
        $data["object_type_id"] = $def->getId();

        // Set data as JSON (we are replacing columsn with this for custom fields)
        $data['field_data'] = json_encode($entity->toArray());

        $targetTable = $def->getTable();

        // Determine if we are looking at the deleted or active partition
        $targetTable .= ($entity->isDeleted()) ? "_del" : "_act";

        /*
         * If the deleted status has not changed then update row.
         * The last condition checks if update is greater than 1, since 1 will be the value
         * of the very first save. It is possible that a user set a specific ID of an entity
         * when creating it. This will not matter at all for partitioned tables since it will
         * automatically delete before inserting, but for custom tables it could cause a bug
         * where it tried to update an ID that does not exist.
         */
        if ($entity->getId() && !$entity->fieldValueChanged("f_deleted") && $entity->getValue("revision") > 1) {
            $this->database->update($targetTable, $data, ['id' => $entity->getId()]);
        } else {
            // Clean out old record if it exists in a different partition
            if ($entity->getId()) {
                $this->database->query(
                    'DELETE FROM ' . $def->getTable() . ' WHERE id=:entity_id',
                    ['entity_id' => $entity->getId()]
                );
            }

            // Now try saving the entity
            try {
                $entityId = $this->database->insert($targetTable, $data);
                // if ($entity->getId() && $entityId != $entity->getId()) {
                //     throw new \RuntimeException(
                //         "Returned id from DB insert $entityId is different " .
                //             "than the id saved: " . $entity->getId()
                //     );
                // }
                if (!$entity->getId()) {
                    $entity->setValue('id', $entityId);
                }
            } catch (DatabaseQueryException $ex) {
                throw new \RuntimeException(
                    'Could not insert entity due to a database error: ' . $ex->getMessage() .
                        ', data: ' . var_export($data, true)
                );
            }
        }

        // If we were unable to save the ID then return false (should probably be an exception?)
        if (!$entity->getId()) {
            return false;
        }

        // Handle autocreate folders - only has to fire the very first time
        // TODO: We should either move this into an abstract function since it is non-db-specific
        //       business logic, or better yet - delete it if we can retire autocreatename
        //       in exchange for using the more generic attachments field that every entity has
        foreach ($all_fields as $fname => $fdef) {
            if ($fdef->type == "object" && $fdef->subtype == "folder"
                && $fdef->autocreate && $fdef->autocreatebase && $fdef->autocreatename
                && !$entity->getValue($fname) && $entity->getValue($fdef->autocreatename)) {
                // Make a folder for the entity
                $fileSystem = $this->account->getServiceManager()->get(FileSystemFactory::class);

                // TODO: We should automatically set the path for entity folders
                $folder = $fileSystem->openFolder(
                    $fdef->autocreatebase . "/" . $entity->getValue($fdef->autocreatename),
                    true
                );

                // Update the entity and table
                if ($folder->getId()) {
                    $entity->setValue($fname, $folder->getId());

                    $this->database->update(
                        $targetTable,
                        [$fname => $folder->getId()],
                        ['id' => $entity->getId()]
                    );
                }
            }
        }

        // Handle updating reference membership if needed
        foreach ($all_fields as $fname => $fdef) {
            if ($fdef->type == Field::TYPE_GROUPING_MULTI) {
                $this->updateObjectGroupingMulti($entity, $fdef);
            }

            // Handle object associations
            if ($fdef->isObjectReference()) {
                $this->updateObjectAssociations($entity, $fdef);
            }
        }

        return $entity->getId();
    }

    /**
     * Update object groupings for a multi-value field
     *
     * @param EntityInterface $entity
     * @param Field $field
     */
    private function updateObjectGroupingMulti(EntityInterface $entity, Field $field)
    {
        /*
         * First clear out all existing values in the union table because trying to update
         * them would require more work than its worth.
         */
        $whereParams = [];
        // ['ref_table']["this"] is almost always just 'id'
        $whereParams[$field->fkeyTable['ref_table']["this"]] = $entity->getId();

        // object_type_id and field_id is needed for generic groupings
        if ($field->subtype == "object_groupings") {
            $whereParams['object_type_id'] = $entity->getDefinition()->getId();
            $whereParams['field_id'] = $field->id;
        }

        $this->database->delete(
            $field->fkeyTable['ref_table']['table'],
            $whereParams
        );

        // Now insert the rows to associate this entity with the foreign grouping
        $values = $entity->getValue($field->name);
        if (is_array($values)) {
            foreach ($values as $val) {
                if ($val) {
                    $dataToInsert = [];
                    $dataToInsert[$field->fkeyTable['ref_table']['ref']] = $val;
                    $dataToInsert[$field->fkeyTable['ref_table']["this"]] = $entity->getId();

                    if ($field->subtype == "object_groupings") {
                        $dataToInsert['object_type_id'] = $entity->getDefinition()->getId();
                        $dataToInsert['field_id'] = $field->id;
                    }

                    $this->database->insert(
                        $field->fkeyTable['ref_table']['table'],
                        $dataToInsert
                    );
                }
            }
        }
    }

    /**
     * Update associations that connect entities to each other
     *
     * @param EntityInterface $entity
     * @param Field $field
     * @throws DatabaseQueryException
     */
    private function updateObjectAssociations(EntityInterface $entity, Field $field)
    {
        $def = $entity->getDefinition();
        $defLoader = $this->getAccount()->getServiceManager()->get(EntityDefinitionLoaderFactory::class);

        /*
         * Just like with fkey_multi above, we first clear out all existing values in the
         * union table because trying to update them would require more work than its worth.
         */
        $this->database->delete(
            'object_associations',
            ['object_id' => $entity->getId(), 'type_id' => $def->getId(), 'field_id' => $field->id]
        );
       
        // Set values
        $mvalues = $entity->getValue($field->name);
        if (is_array($mvalues)) {
            foreach ($mvalues as $val) {
                $subtype = null; // Set the initial value of subtype to null

                $otid = -1;
                if ($field->subtype) {
                    $subtype = $field->subtype;
                    $objid = $val;
                } else {
                    $parts = $entity->decodeObjRef($val);
                    if ($parts['obj_type'] && $parts['id']) {
                        $subtype = $parts['obj_type'];
                        $objid = $parts['id'];
                    }
                }

                if ($subtype) {
                    $assocDef = $defLoader->get($subtype);
                    if ($assocDef->getId() && is_numeric($objid)) {
                        $this->database->insert(
                            'object_associations',
                            [
                                'object_id' => $entity->getId(),
                                'type_id' => $def->getId(),
                                'assoc_type_id' => $assocDef->getId(),
                                'assoc_object_id' => $objid,
                                'field_id' => $field->id,
                            ]
                        );
                    }
                }
            }
        } elseif ($mvalues) {
            if ($field->subtype) {
                $assocDef = $defLoader->get($field->subtype);
                if ($assocDef->getId()) {
                    $this->database->insert(
                        'object_associations',
                        [
                            'object_id' => $entity->getId(),
                            'type_id' => $def->getId(),
                            'assoc_type_id' => $assocDef->getId(),
                            'assoc_object_id' => $mvalues,
                            'field_id' => $field->id,
                        ]
                    );
                }
            } else {
                $parts = $entity->decodeObjRef($mvalues);
                if ($parts['obj_type'] && $parts['id']) {
                    $assocDef = $defLoader->get($parts['obj_type']);
                    if ($assocDef->getId() && $parts['id']) {
                        $this->database->insert(
                            'object_associations',
                            [
                                'object_id' => $entity->getId(),
                                'type_id' => $def->getId(),
                                'assoc_type_id' => $assocDef->getId(),
                                'assoc_object_id' => $parts['id'],
                                'field_id' => $field->id,
                            ]
                        );
                    }
                }
            }
        }
    }

    /**
     * Convert fields to column names for saving table and escape for insertion/updates
     *
     * @param EntityInterface $entity The entity we are saving
     * @return array("col_name"=>"value")
     */
    private function getDataToInsertFromEntity(EntityInterface $entity)
    {
        $ret = array();
        $all_fields = $entity->getDefinition()->getFields();

        foreach ($all_fields as $fname => $fdef) {
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
                            $ret[$fname] = (int)$val;
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
                    $ret[$fname] = (is_numeric($val)) ? $val : null;
                    break;
                case 'object':
                    if ($fdef->subtype) {
                        // If there is a subtype then the value should be an int or null
                        $ret[$fname] = (is_numeric($val)) ? $val : null;
                    } else {
                        // object references with both the type and id are stored as a string
                        $ret[$fname] = $val;
                    }
                    break;
                default:
                    $ret[$fname] = $val;
                    break;
            }

            // Set fval cache so we do not have to do crazy joins across tables
            if ($fdef->type == "fkey" || $fdef->type == "fkey_multi" ||
                $fdef->type == "object" || $fdef->type == "object_multi") {
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
     * Decode a json encoded string into an array
     *
     * @param string $val The encoded string
     * @return array on success, null on failure
     */
    private function unserialize($val)
    {
        if ($val == null || $val == "") {
            return null;
        }

        return json_decode($val, true);
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
     * Load foreign values from the database
     *
     * @param EntityDefinition_Field $fdef The field we are getting foreign lavel/title for
     * @param string $value Raw value from field if exists
     * @param string $oid The object id we are getting values for
     * @param string $otid The object type id id we are getting values for
     * @return array('keyid'=>'value/name')
     */
    private function getForeignKeyDataFromDb($fdef, $value, $oid, $otid)
    {
        $ret = array();

        if ($fdef->type == "fkey" && $value) {
            $sql = 'SELECT ' . $fdef->fkeyTable['key'] . ' as id, ' .
                $fdef->fkeyTable['title'] . ' as name ' .
                'FROM ' . $fdef->subtype . ' WHERE ' . $fdef->fkeyTable['key'] . '=:key_value';
            $result = $this->database->query($sql, ['key_value' => $value]);
            if ($result->rowCount()) {
                $row = $result->fetch();
                $ret[(string)$row['id']] = $row['name'];
            } else {
                // The foreign object is no longer in the foreign table, just use id
                $ret[$value] = $value;
            }
        }

        if ($fdef->type == "fkey_multi") {
            $memTbl = $fdef->fkeyTable['ref_table']['table'];
            $sql = 'SELECT ' . $fdef->subtype . '.' . $fdef->fkeyTable['key'] . ' as id, ' .
                $fdef->subtype . '.' . $fdef->fkeyTable['title'] . ' as name' .
                ' FROM ' . $fdef->subtype . ', ' . $memTbl .
                ' WHERE ' .
                $fdef->subtype . '.' . $fdef->fkeyTable['key'] . '=' .
                $memTbl . '.' . $fdef->fkeyTable['ref_table']['ref'] .
                ' AND ' . $fdef->fkeyTable['ref_table']["this"] . '=:oid';

            $result = $this->database->query($sql, ['oid' => $oid]);
            if ($result->rowCount()) {
                $row = $result->fetch();
                $ret[(string)$row['id']] = $row['name'];
            }
        }

        /*
         * Update the names of any object references
         *
         * The below solution is grossly inefficient but should only be necessary for very old
         * objects and then will be cached by the loader in the caching datamapper.
         * Eventually we will just remove it along with this entire function.
         */
        if ($fdef->type == "object" && $fdef->subtype && $this->getAccount()->getServiceManager() && $value) {
            $entity = $this->getAccount()->getServiceManager()->get(EntityLoaderFactory::class)->get($fdef->subtype, $value);
            if ($entity) {
                $ret[(string)$value] = $entity->getName();
            } else {
                $log = $this->getAccount()->getApplication()->getLog();
                $log->error("Could not load {$fdef->subtype}.{$value} to update foreign value");
            }
        } elseif (($fdef->type == "object" && !$fdef->subtype) || $fdef->type == "object_multi") {
            $sql = 'SELECT ' .
                'assoc_type_id, assoc_object_id, app_object_types.name as obj_name ' .
                'FROM object_associations INNER JOIN app_object_types ' .
                'ON (object_associations.assoc_type_id = app_object_types.id) ' .
                'WHERE field_id=:field_id AND type_id=:type_id AND object_id=:oid ' .
                ' LIMIT 1000';
            $whereConditions = ['field_id' => $fdef->id, 'type_id' => $otid, 'oid' => $oid];
            $result = $this->database->query($sql, $whereConditions);
            foreach ($result->fetchAll() as $row) {
                $oname = "";
                $oname = $row['obj_name'];
                $idval = $oname . ":" . $row["assoc_object_id"];

                // If subtype is set in the field, then only the id of the object is stored
                if ($fdef->subtype) {
                    $oname = $fdef->subtype;
                    $idval = (string)$row['assoc_object_id'];
                }
                
                /*
                 * Set the value to null since we cant get the referenced entity name for now.
                 * Let the caller handle getting the name of the referenced entity
                 */
                $ret[(string)$idval] = null;
            }
        }

        return $ret;
    }

    /**
     * Check to see if this object id was moved or merged into a different id
     *
     * @param EntityDefinition $def The defintion of this object type
     * @param string $id The id of the object that no longer exists - may have moved
     * @return string new Entity id if moved, otherwise false
     */
    protected function entityHasMoved($def, $id)
    {
        if (!$id) {
            return false;
        }

        $sql = 'SELECT moved_to FROM objects_moved WHERE ' .
            'object_type_id=:object_type_id AND object_id=:object_id';
        $result = $this->database->query($sql, [
            'object_type_id' => $def->getId(),
            'object_id' => $id
        ]);
        if ($result->rowCount() > 0) {
            $row = $result->fetch();
            return $row['moved_to'];
        }

        return false;
    }

    /**
     * Set this object as having been moved to another object
     *
     * @param EntityDefinition $def The defintion of this object type
     * @param string $fromId The id to move
     * @param string $toId The unique id of the object this was moved to
     * @return bool true on succes, false on failure
     * @throws DatabaseQueryException if query fails
     */
    public function setEntityMovedTo(EntityDefinition $def, $fromId, $toId)
    {
        if (!$fromId || $fromId == $toId) { // never allow circular reference or blank values
            return false;
        }

        $data = [
            'object_type_id' => $def->getId(),
            'object_id' => $fromId,
            'moved_to' => $toId,
        ];
        $this->database->insert('objects_moved', $data);

        // Update the referenced entities
        $this->updateOldReferences($def, $fromId, $toId);

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
                    $newFieldValue = $toId;
                }

                // Encode object type and id with generic obj_type:obj_id
                if (empty($field->subtype)) {
                    $oldFieldValue = $definition->getObjType() . ':' . $fromId;
                    $newFieldValue = $definition->getObjType() . ':' . $toId;
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

        if ($entity->getValue("revision") && $entity->getId() && $def->getId()) {
            $insertData = [
                'object_id' => $entity->getId(),
                'object_type_id' => $def->getId(),
                'revision' => $entity->getValue("revision"),
                'ts_updated' => 'now',
                'data' => $data = serialize($entity->toArray()),
            ];
            $this->database->insert('object_revisions', $insertData);
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

        $ret = array();

        $results = $this->database->query(
            'SELECT id, revision, data FROM object_revisions ' .
                'WHERE object_type_id=:object_type_id AND object_id=:object_id',
            ['object_type_id' => $def->getId(), 'object_id' => $id]
        );
        foreach ($results->fetchAll() as $row) {
            $ent = $this->getAccount()->getServiceManager()->get(EntityFactoryFactory::class)->create($objType);
            $ent->fromArray(unserialize($row['data']));
            $ret[$row['revision']] = $ent;
        }

        return $ret;
    }
}
