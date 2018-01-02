<?php

namespace Netric\Entity\DataMapper;

use Netric\Db\Relational\Exception\DatabaseQueryException;
use Netric\Entity\DataMapperAbstract;
use Netric\Entity\DataMapperInterface;
use Netric\Db\Relational\RelationalDbInterface;
use Netric\Entity\EntityInterface;
use DateTime;

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
     * Legacy handle to Netric\Db\Db
     *
     * @var null
     */
    private $dbh = null;

    /**
     * Setup this class called from the parent constructor
     */
    protected function setUp()
    {
        $this->database = $this->account->getServiceManager()->get('Netric/Db/Relational/RelationalDb');
        $this->dbh = $this->account->getServiceManager()->get('Db');
    }

    /**
     * Open object by id
     *
     * @var Entity $entity The entity to load data into
     * @var string $id The Id of the object
     * @return bool true on success, false on failure
     */
    protected function fetchById(&$entity, $id)
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

        // Load data for foreign keys
        $row = $result->fetch();
        $all_fields = $def->getFields();
        foreach ($all_fields as $fname => $fdef) {

            // Populate values and foreign values for foreign entries if not set
            if ($fdef->type == "fkey" || $fdef->type == "object" || $fdef->type == "fkey_multi" || $fdef->type == "object_multi") {
                $mvals = null;

                // If fval is not set which should only occur on old objects prior to caching data in version 2
                if (!$row[$fname . "_fval"] || ($row[$fname . "_fval"] == '[]' && $row[$fname] != '[]' && $row[$fname] != '')) {
                    $mvals = $this->getForeignKeyDataFromDb($fdef, $row[$fname], $entity->getId(), $def->getId());
                    $row[$fname . "_fval"] = ($mvals) ? json_encode($mvals) : "";
                }

                // set values of fkey_multi and object_multi fields as array of id(s)
                if ($fdef->type == "fkey_multi" || $fdef->type == "object_multi") {
                    if ($row[$fname]) {
                        $parts = $this->decodeFval($row[$fname]);
                        if ($parts !== false) {
                            $row[$fname] = $parts;
                        }
                    }

                    // Was not set in the column, try reading from mvals list that was generated above
                    if (!$row[$fname]) {
                        if (!$mvals && $row[$fname . "_fval"])
                            $mvals = $this->decodeFval($row[$fname . "_fval"]);

                        if ($mvals) {
                            foreach ($mvals as $id => $mval)
                                $row[$fname][] = $id;
                        }
                    }
                }

                // Get object with no subtype - we may want to store this locally eventually
                // so check to see if the data is not already defined
                if (!$row[$fname] && $fdef->type == "object" && !$fdef->subtype) {
                    if (!$mvals && $row[$fname . "_fval"])
                        $mvals = $this->decodeFval($row[$fname . "_fval"]);

                    if ($mvals) {
                        foreach ($mvals as $id => $mval)
                            $row[$fname] = $id; // There is only one value but it is assoc
                    }
                }
            }

            switch ($fdef->type) {
                case "bool":
                    $row[$fname] = ($row[$fname] == 't') ? true : false;
                    break;
                case "date":
                case "timestamp":
                    $row[$fname] = ($row[$fname]) ? strtotime($row[$fname]) : null;
                    break;
            }

            // Check if we have an fkey label/name associated with column ids - these are cached in the object
            $fkeyValueName = (isset($row[$fname . "_fval"])) ? $this->decodeFval($row[$fname . "_fval"]) : null;

            // Set entity value
            if (isset($row[$fname]))
                $entity->setValue($fname, $row[$fname], $fkeyValueName);
        }

        return true;
    }

    /**
     * Delete object by id
     *
     * @var Entity $entity The entity to load data into
     * @return bool true on success, false on failure
     */
    protected function deleteHard(&$entity)
    {
        // Only delete existing objects
        if (!$entity->getId())
            return false;

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
        $this->database->query(
            'DELETE FROM object_associations WHERE ' .
            '(object_id=:object_id and type_id=:type_id) OR ' .
            '(assoc_object_id=:object_id and assoc_type_id=:type_id)',
            [
                'object_id' => $entity->getId(), 'type_id' => $def->getId(),
            ]
        );

        // We just need to make sure the main object was deleted
        return ($result->rowCount() > 0);
    }

    /**
     * Delete object by id
     *
     * @var Entity $entity The entity to load data into
     * @return bool true on success, false on failure
     */
    protected function deleteSoft(&$entity)
    {
        // Update the deleted flag and save
        $entity->setValue("f_deleted", true);
        $ret = $this->save($entity);
        return ($ret == false) ? false : true;
    }

    /**
     * Get object definition based on an object type
     *
     * @param string $objType The object type name
     * @param string $fieldName The field name to get grouping data for
     * @return EntityGrouping[]
     */
    public function getGroupings($objType, $fieldName, $filters = array())
    {
        $def = $this->getAccount()->getServiceManager()->get("EntityDefinitionLoader")->get($objType);
        if (!$def)
            throw new \Exception("Entity could not be loaded");

        $field = $def->getField($fieldName);

        if ($field->type != "fkey" && $field->type != "fkey_multi")
            throw new \Exception("$objType:$fieldName:" . $field->type . " is not a grouping (fkey or fkey_multi) field!");

        $dbh = $this->dbh;

        if ($field->subtype == "object_groupings")
            $cnd = "object_type_id='" . $def->getId() . "' and field_id='" . $field->id . "' ";
        else
            $cnd = "";

        // Check filters to refine the results - can filter by parent object like project id for cases or tasks
        if (isset($field->fkeyTable['filter'])) {
            foreach ($field->fkeyTable['filter'] as $grouping_field => $object_field) {
                if (isset($filters[$object_field])) {
                    if ($cnd) {
                        $cnd .= " and ";
                    }

                    /*
                     * When passing the filter (last param with owner value)
                     * the key name is the name of the property in the entity, in this case
                     * email_message.owner_id and the value to query for. The entity definition
                     * for the grouping will map the entity field value to the grouping value if
                     * the names are different like - groupings.user_id=email_message.owner_id
                     */
                    $cnd .= " $grouping_field='" . $filters[$object_field] . "' ";

                } else if (isset($filters[$grouping_field])) {
                    // A filer can also come in as the grouping field name rather than the object
                    if ($cnd) {
                        $cnd .= " and ";
                    }
                    $cnd .= " $grouping_field='" . $filters[$grouping_field] . "' ";
                }
            }
        }

        // Filter results to this user of the object is private
        if ($def->isPrivate && !isset($filters["user_id"]) && !isset($filters["owner_id"])) {
            throw new \Exception("Private entity type called but grouping has no filter defined - " . $def->getObjType());
        }

        $sql = "SELECT * FROM " . $field->subtype;

        if ($cnd)
            $sql .= " WHERE $cnd ";

        if ($this->dbh->columnExists($field->subtype, "sort_order"))
            $sql .= " ORDER BY sort_order, " . (($field->fkeyTable['title']) ? $field->fkeyTable['title'] : $field->fkeyTable['key']);
        else
            $sql .= " ORDER BY " . (($field->fkeyTable['title']) ? $field->fkeyTable['title'] : $field->fkeyTable['key']);

        // Technically, the limit of groupings is 1000 per field, but just to be safe
        $sql .= " LIMIT 10000";

        $groupings = new \Netric\EntityGroupings($objType, $fieldName, $filters);

        $result = $dbh->Query($sql);
        $num = $this->dbh->getNumRows($result);
        for ($i = 0; $i < $num; $i++) {
            $row = $this->dbh->getRow($result, $i);

            $group = new \Netric\EntityGroupings\Group();
            $group->id = $row[$field->fkeyTable['key']];
            $group->name = $row[$field->fkeyTable['title']];
            $group->isHeiarch = (isset($field->fkeyTable['parent'])) ? true : false;
            if (isset($field->fkeyTable['parent']) && isset($row[$field->fkeyTable['parent']]))
                $group->parentId = $row[$field->fkeyTable['parent']];
            $group->color = (isset($row['color'])) ? $row['color'] : "";
            if (isset($row['sort_order']))
                $group->sortOrder = $row['sort_order'];
            $group->isSystem = (isset($row['f_system']) && $row['f_system'] == 't') ? true : false;
            $group->commitId = (isset($row['commit_id'])) ? $row['commit_id'] : 0;

            //$item['f_closed'] = (isset($row['f_closed']) && $row['f_closed']=='t') ? true : false;

            // Add all additional fields which are usually used for filters
            foreach ($row as $pname => $pval) {
                if (!$group->getValue($pname))
                    $group->setValue($pname, $pval);
            }

            // Make sure the group is not marked as dirty
            $group->setDirty(false);

            $groupings->add($group);
        }

        // TODO: we need to think about how we can manage default groupings
        // Make sure that default groupings exist (if any)
        //if (!$parent && sizeof($conditions) == 0) // Do not create default groupings if data is filtered
        //	$ret = $this->verifyDefaultGroupings($fieldName, $data, $nameValue);
        //else
        //	$ret = $data;

        return $groupings;
    }

    /**
     * Save groupings
     *
     * @param \Netric\EntityGroupings
     * @param int $commitId The commit id of this save
     * @return array("changed"=>int[], "deleted"=>int[]) Log of changed groupings
     */
    protected function _saveGroupings(\Netric\EntityGroupings $groupings, $commitId)
    {
        $def = $this->getAccount()->getServiceManager()->get("EntityDefinitionLoader")->get($groupings->getObjType());
        if (!$def)
            return false;

        $field = $def->getField($groupings->getFieldName());

        $ret = array("deleted" => array(), "changed" => array());

        $toDelete = $groupings->getDeleted();
        foreach ($toDelete as $grp) {
            $this->database->query(
                'DELETE FROM ' . $field->subtype . ' WHERE id=:id',
                ['id' => $grp->id]
            );

            // Log here
            $ret['deleted'][$grp->id] = $grp->commitId;
        }

        $toSave = $groupings->getChanged();
        foreach ($toSave as $grp) {
            // Cache for updates to object_sync
            $lastCommitId = $grp->getValue("commitId");

            // Set the new commit id
            $grp->setValue("commitId", $commitId);

            if ($this->saveGroup($def, $field, $grp)) {
                $grp->setDirty(false);
                // Log here
                $ret['changed'][$grp->id] = $lastCommitId;
            }
        }

        return $ret;
    }

    /**
     * Save a new or existing group
     *
     * @param \Netric\EntityDefinition $def Entity type definition
     * @param \Netric\EntityDefinition\Field $field The field we are saving a grouping for
     * @param \Netric\EntityGroupings\Group $grp The grouping to save
     * @return bool true on sucess, false on failure
     */
    private function saveGroup($def, $field, \Netric\EntityGroupings\Group $grp)
    {
        if (!$field)
            return false;

        if ($field->type != "fkey" && $field->type != "fkey_multi")
            return false;

        $tableData = [];

        if (isset($grp->uname)) {
            throw new \RuntimeException('NO UNAME!!!');
        }

        if ($grp->name && $field->fkeyTable['title']) {
            $tableData[$field->fkeyTable['title']] = $grp->name;
        }

        if ($grp->color && $this->dbh->columnExists($field->subtype, "color")) {
            $tableData['color'] = $grp->color;
        }

        if ($grp->isSystem && $this->dbh->columnExists($field->subtype, "f_system")) {
            $tableData['f_system'] = $grp->isSystem;
        }

        if ($grp->sortOrder && $this->dbh->columnExists($field->subtype, "sort_order")) {
            $tableData['sort_order'] = $grp->sortOrder;
        }

        if ($grp->parentId && isset($field->fkeyTable['parent'])) {
            $tableData[$field->fkeyTable['parent']] = $grp->parentId;
        }

        if ($grp->commitId) {
            $tableData['commit_id'] = $grp->commitId;
        }

        if ($field->subtype == "object_groupings") {
            $tableData['object_type_id'] = $def->getId();
            $tableData['field_id'] = $field->id;
        }

        $data = $grp->toArray();

        foreach ($data["filter_fields"] as $name => $value) {
            // Make sure that the column name does not exists yet
            if (array_key_exists($name, $tableData)) {
                continue;
            }

            if ($value && $this->dbh->columnExists($field->subtype, $name)) {
                $tableData[$name] = $value;
            }
        }

        // Execute query
        if (count($tableData) == 0) {
            throw new \RuntimeException('Cannot save grouping - invalid data ' . var_export($grp, true));
        }

        if ($grp->id) {
            $this->database->update($field->subtype, $tableData, ['id' => $grp->id]);
        } else {
            $grp->id = $this->database->insert($field->subtype, $tableData);
        }

        return true;
    }

    /**
     * Save object data
     *
     * @param Entity $entity The entity to save
     * @return string|bool entity id on success, false on failure
     */
    protected function saveData($entity)
    {
        $def = $entity->getDefinition();

        // Convert to cols=>vals escaped array
        $data = $this->getDataToInsertFromEntity($entity);
        $all_fields = $def->getFields();

        // Try to manipulate data to correctly build the sql statement based on custom table definitions
        if (!$def->isCustomTable())
            $data["object_type_id"] = $def->getId();

        $targetTable = $def->getTable();

        if (!$def->isCustomTable() && $entity->isDeleted())
            $targetTable .= "_del";
        else if (!$def->isCustomTable())
            $targetTable .= "_act";

        /*
         * If we are using a custom table or the deleted status has not changed
         * on a generic object table then update row.
         * The last condition checks if update is greater than 1, since 1 will be the value
         * of the very first save. It is possible that a user set a specific ID of an entity
         * when creating it. This will not matter at all for partitioned tables since it will
         * automatically delete before inserting, but for custom tables it could cause a bug
         * where it tried to update an ID that does not exist.
         */
        if ($entity->getId() && ($def->isCustomTable() || (!$entity->fieldValueChanged("f_deleted") && !$def->isCustomTable())) && $entity->getValue("revision") > 1) {
            $this->database->update($targetTable, $data, ['id' => $entity->getId()]);
        } else {
            // Clean out old record if it exists in a different partition
            if ($entity->getId() && !$def->isCustomTable()) {
                $this->database->query(
                    'DELETE FROM ' . $def->getTable() . ' WHERE id=:entity_id',
                    ['entity_id' => $entity->getId()]
                );
            }

            // Now try saving the entity
            try {
                $entityId = $this->database->insert($targetTable, $data);
                $entity->setValue('id', $entityId);
            } catch (DatabaseQueryException $ex) {
                throw new \RuntimeException(
                    'Could not insert entity due to a database error: ' . $ex->getMessage()
                );
            }
        }

        // If we were unable to save the ID then return false (should probably be an exception?)
        if (!$entity->getId()) {
            return false;
        }

        // handle fkey_multi && auto fields
        // ----------------------------------

        // Handle autocreate folders - only has to fire the very first time
        foreach ($all_fields as $fname => $fdef) {
            if ($fdef->type == "object" && $fdef->subtype == "folder"
                && $fdef->autocreate && $fdef->autocreatebase && $fdef->autocreatename
                && !$entity->getValue($fname) && $entity->getValue($fdef->autocreatename)) {
                // Make a folder for the entity
                $fileSystem = $this->account->getServiceManager()->get("Netric/FileSystem/FileSystem");

                // TODO: We should automatically set the path for entity folders
                $folder = $fileSystem->openFolder(
                    $fdef->autocreatebase . "/" . $entity->getValue($fdef->autocreatename),
                    true
                );

                // Update the entity and table
                if ($folder->getId()) {
                    $entity->setValue($fname, $folder->getId());

                    $this->database->update(
                        $targetTable, [$fname=>$folder->getId()], ['id' => $entity->getId()]
                    );
                }
            }
        }

        // Handle updating reference membership if needed
        $defLoader = $this->getAccount()->getServiceManager()->get("EntityDefinitionLoader");
        foreach ($all_fields as $fname => $fdef) {
            if ($fdef->type == "fkey_multi") {

                /*
                 * First clear out all existing values in the union table because trying to update
                 * them would require more work than its worth.
                 */
                $whereParams = [];
                // ['ref_table']["this"] is almost always just 'id'
                $whereParams[$fdef->fkeyTable['ref_table']["this"]] = $entity->getId();

                // object_type_id and field_id is needed for generic groupings
                if ($fdef->subtype == "object_groupings") {
                    $whereParams['object_type_id'] = $def->getId();
                    $whereParams['field_id'] =$fdef->id;
                }

                $this->database->delete(
                    $fdef->fkeyTable['ref_table']['table'],
                    $whereParams
                );


                /*
                 * Now insert the rows to associate this entity with the foreign grouping
                 */
                $mvalues = $entity->getValue($fname);
                if (is_array($mvalues)) {
                    foreach ($mvalues as $val) {
                        if ($val) {
                            $dataToInsert = [];
                            $dataToInsert[$fdef->fkeyTable['ref_table']['ref']] = $val;
                            $dataToInsert[$fdef->fkeyTable['ref_table']["this"]] = $entity->getId();

                            if ($fdef->subtype == "object_groupings") {
                                $dataToInsert['object_type_id'] = $def->getId();
                                $dataToInsert['field_id'] = $fdef->id;
                            }

                            $this->database->insert(
                                $fdef->fkeyTable['ref_table']['table'],
                                $dataToInsert
                            );
                        }
                    }
                }
            }

            // Handle object associations
            if ($fdef->type == "object_multi" || $fdef->type == "object") {
                /*
                 * Just like with fkey_multi above, we first clear out all existing values in the
                 * union table because trying to update them would require more work than its worth.
                 */
                $this->database->delete(
                    'object_associations',
                    ['object_id' => $entity->getId(), 'type_id' => $def->getId(), 'field_id' => $fdef->id]
                );

                // Set values
                $mvalues = $entity->getValue($fname);
                if (is_array($mvalues)) {
                    foreach ($mvalues as $val) {
                        $subtype = null; // Set the initial value of subtype to null

                        $otid = -1;
                        if ($fdef->subtype) {
                            $subtype = $fdef->subtype;
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
                            if ($assocDef->getId() && $objid) {
                                $this->database->insert(
                                    'object_associations',
                                    [
                                        'object_id' => $entity->getId(),
                                        'type_id' => $def->getId(),
                                        'assoc_type_id' => $assocDef->getId(),
                                        'assoc_object_id' => $objid,
                                        'field_id' => $fdef->id,
                                    ]
                                );
                            }
                        }
                    }
                } else if ($mvalues) {
                    if ($fdef->subtype) {
                        $assocDef = $defLoader->get($fdef->subtype);
                        if ($assocDef->getId()) {
                            $this->database->insert(
                                'object_associations',
                                [
                                    'object_id' => $entity->getId(),
                                    'type_id' => $def->getId(),
                                    'assoc_type_id' => $assocDef->getId(),
                                    'assoc_object_id' => $mvalues,
                                    'field_id' => $fdef->id,
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
                                        'field_id' => $fdef->id,
                                    ]
                                );
                            }
                        }
                    }
                }
            }
        }

        return $entity->getId();
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
                    }
                    break;
                case 'timestamp':
                    // All timestamp fields are epoch timestamps
                    if (is_numeric($val) && $val > 0) {
                        $ret[$fname] = date(DateTime::ATOM, $val);
                    }
                    break;
                case 'text':
                    $tmpval = $val;
                    // Check if the field has a limited length
                    if (is_numeric($fdef->subtype)) {
                        if (strlen($tmpval) > $fdef->subtype)
                            $tmpval = substr($tmpval, 0, $fdef->subtype);
                    }
                    $ret[$fname] = $tmpval;
                    break;
                case 'bool':
                    $ret[$fname] = ($val === true);
                    break;
                case 'object':
                case 'fkey':
                default:
                    $ret[$fname] = $val;
                    break;
            }

            // Set fval cache so we do not have to do crazy joins across tables
            if ($fdef->type == "fkey" || $fdef->type == "fkey_multi" || $fdef->type == "object" || $fdef->type == "object_multi") {
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
     * Decode fval which is saved as json encoded string
     *
     * @param string $val The encoded string
     * @return array on success, null on failure
     */
    private function decodeFval($val)
    {
        if ($val == null || $val == "")
            return null;

        return json_decode($val, true);
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
        $dbh = $this->dbh;
        $ret = array();

        if ($fdef->type == "fkey" && $value) {
            $sql = 'SELECT ' . $fdef->fkeyTable['key'] . ' as id, ' .
                $fdef->fkeyTable['title'] . 'as name ' .
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
                $fdef->subtype . '.' .  $fdef->fkeyTable['title'] . ' as name' .
                ' FROM ' . $fdef->subtype . ', ' . $memTbl .
                ' WHERE ' .
                    $fdef->subtype . '.' . $fdef->fkeyTable['key'] . '=' .
                    $memTbl . '.' . $fdef->fkeyTable['ref_table']['ref'] .
                    ' AND ' . $fdef->fkeyTable['ref_table']["this"] . '=:oid';

            $result = $this->database->query($sql, ['oid'=>$oid]);
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
            $entity = $this->getAccount()->getServiceManager()->get("EntityLoader")->get($fdef->subtype, $value);
            if ($entity) {
                $ret[(string)$value] = $entity->getName();
            } else {

                $log = $this->getAccount()->getApplication()->getLog();
                $log->error("Could not load {$fdef->subtype}.{$value} to update foreign value");
            }

        } else if (($fdef->type == "object" && !$fdef->subtype) || $fdef->type == "object_multi") {
            $query = "select assoc_type_id, assoc_object_id, app_object_types.name as obj_name
							 from object_associations inner join app_object_types on (object_associations.assoc_type_id = app_object_types.id)
							 where field_id='" . $fdef->id . "' and type_id='" . $otid . "'
							 and object_id='" . $oid . "' LIMIT 1000";
            $result = $dbh->query($query);
            for ($i = 0; $i < $dbh->getNumRows($result); $i++) {
                $row = $dbh->getRow($result, $i);

                $oname = "";

                // If subtype is set in the field, then only the id of the object is stored
                if ($fdef->subtype) {
                    $oname = $fdef->subtype;
                    $idval = (string)$row['assoc_object_id'];
                } else {
                    $oname = $row['obj_name'];
                    $idval = $oname . ":" . $row["assoc_object_id"];
                }

                /* Removed this code since it is causing a circular reference
                 *
                 * When an entity (e.g. User) has a referenced entity (e.g File),
                 * EntityLoader will try to get the referenced entity data from the datamapper (if referenced entity is not yet cached)
                 * And then File entity will try to get the User Entity which will cause a circular reference
                    if ($oname)
                    {
                        $entity = $this->getAccount()->getServiceManager()->get("EntityLoader")->get($oname, $row['assoc_object_id']);

                        // Update if field is not referencing an entity that no longer exists
                        if ($entity)
                            $ret[(string)$idval] = $entity->getName();
                    }
                 */

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
     * @return string new Entity id if moved, otherwise false
     */
    protected function entityHasMoved($def, $id)
    {
        if (!$id)
            return false;

        $sql = 'SELECT moved_to FROM objects_moved WHERE ' .
               'object_type_id=:object_type_id AND object_id=:object_id';
        $result = $this->database->query($sql, ['object_type_id' => $def->getId(), 'object_id' => $id]);
        if ($result->rowCount() > 0) {
            $row = $result->fetch();
            $moved_to = $row['moved_to'];

            // Kill circular references - objects moved to each other
            if (in_array($id, $this->movedToRef))
                return false;

            $this->movedToRef[] = $moved_to;

            return $moved_to;
        }

        return false;
    }

    /**
     * Set this object as having been moved to another object
     *
     * @param EntityDefinition $def The defintion of this object type
     * @param string $fromId The id to move
     * @param stirng $toId The unique id of the object this was moved to
     * @return bool true on succes, false on failure
     * @throws DatabaseQueryException if query fails
     */
    public function setEntityMovedTo(&$def, $fromId, $toId)
    {
        if (!$fromId || $fromId == $toId) // never allow circular reference or blank values
            return false;

        $data = [
            'object_type_id' => $def->getId(),
            'object_id' => $fromId,
            'moved_to' => $toId,
        ];
        $this->database->insert('objects_moved', $data);

        // If it fails an exception will be thrown
        return true;
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
        if (!$objType || !$id)
            return null;

        $def = $this->getAccount()->getServiceManager()->get("EntityDefinitionLoader")->get($objType);

        if (!$def)
            return null;

        $ret = array();

        $results = $this->database->query(
            'SELECT id, revision, data FROM object_revisions ' .
            'WHERE object_type_id=:object_type_id AND object_id=:object_id',
            ['object_type_id' => $def->getId(), 'object_id' => $id]
        );
        foreach ($results->fetchAll() as $row) {
            $ent = $this->getAccount()->getServiceManager()->get("EntityFactory")->create($objType);
            $ent->fromArray(unserialize($row['data']));
            $ret[$row['revision']] = $ent;
        }

        return $ret;
    }
}
