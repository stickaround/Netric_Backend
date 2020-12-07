<?php
namespace Netric\EntityDefinition\DataMapper;

use Netric\EntityDefinition\EntityDefinition;
use Netric\EntityDefinition\Field;
use Netric\Permissions\Dacl;
use Netric\Db\Relational\RelationalDbContainerInterface;
use Netric\Db\Relational\RelationalDbContainer;
use Netric\Db\Relational\RelationalDbInterface;
use Netric\Db\Relational\Exception\DatabaseQueryException;
use Netric\WorkerMan\WorkerService;
use Aereus\Config\Config;
use Ramsey\Uuid\Uuid;
use RuntimeException;

/**
 * Load and save entity definition data to a relational database
 */
class EntityDefinitionRdbDataMapper extends EntityDefinitionDataMapperAbstract implements EntityDefinitionDataMapperInterface
{
    /**
     * Entity type table
     */
    const ENTITY_TYPE_TABLE = 'entity_definition';

    /**
     * Database container
     *
     * @var RelationalDbContainerInterface
     */
    private $databaseContainer = null;

    /**
     * Construct and initialize dependencies
     *
     * @param RelationalDbContainer $dbContainer Handles the database actions
     */
    public function __construct(
        RelationalDbContainer $dbContainer,
        WorkerService $workerService,
        Config $config
        )
    {        
        $this->databaseContainer = $dbContainer;

        parent::__construct($workerService, $config);
    }

    /**
     * Get active database handle
     *
     * @param string $accountId The account being acted on
     * @return RelationalDbInterface
     */
    private function getDatabase(string $accountId): RelationalDbInterface
    {
        return $this->databaseContainer->getDbHandleForAccountId($accountId);
    }

    /**
     * Open an object definition by name
     *
     * @param string $objType The name of the object type
     * @param string $accountId The account that owns the entity definition
     * 
     * @return EntityDefinition
     */
    public function fetchByName(string $objType, string $accountId)
    {
        if (!$objType || !is_string($objType)) {
            throw new \RuntimeException('objType is a required param');
        }

        $def = new EntityDefinition($objType, $accountId);

        // Get basic object definition
        // ------------------------------------------------------
        $sql = "select
            entity_definition_id, def_data, revision, title,
			f_system, system_definition_hash, dacl, capped,
            default_activity_level, is_private, store_revisions,
            recur_rules, inherit_dacl_ref, parent_field, uname_settings,
            list_title, icon, system_definition_hash
            from " . self::ENTITY_TYPE_TABLE . " 
            where name=:name AND account_id=:account_id";
        $result = $this->getDatabase($accountId)->query($sql, [
            'name' => $objType,
            'account_id' => $accountId
        ]);

        if ($result->rowCount()) {
            $row = $result->fetch();
            $defData = json_decode($row['def_data'], true);
            $def->fromArray($defData);

            if ($row['entity_definition_id'] && !$def->getEntityDefinitionId()) {
                $def->setEntityDefinitionId($row['entity_definition_id']);
            }

            return $def;
        }

        return null;
    }
    
    /**
     * Get an entity definition by id
     *
     * @param string $definitionTypeId Object type of the defintion
     * @param string $accountId The account that owns the entity definition
     * 
     * @return EntityDefinition|null
     */
    public function fetchById(string $definitionTypeId, string $accountId): ?EntityDefinition
    {
        $sql = 'SELECT name FROM ' . self::ENTITY_TYPE_TABLE . ' WHERE entity_definition_id=:entity_definition_id AND account_id=:account_id';
        $result = $this->getDatabase($accountId)->query($sql, [
            'entity_definition_id' => $definitionTypeId,
            'account_id' => $accountId
        ]);

        // The object was not found
        if ($result->rowCount() === 0) {
            return null;
        }

        // Load rows and set values in the entity
        $row = $result->fetch();

        // Load by name
        return $this->fetchByName($row['name'], $accountId);
    }

    /**
     * Delete object definition
     *
     * @param EntityDefinition $def The definition to delete
     * @return bool true on success, false on failure
     */
    public function deleteDef(EntityDefinition $def)
    {
        echo "marl" . $def->getSystem();
        // System objects cannot be deleted
        if ($def->getSystem()) {
            throw new RuntimeException('Unable to delete a system definition');
        }

        // Only delete existing types of course
        if (!$def->getEntityDefinitionId()) {
            return false;
        }

        // Delete object type entries from the database
        // $this->getDatabase($accountId)->delete(
        //     'app_object_type_fields',
        //     ['type_id' => $def->getEntityDefinitionId()]
        // ); // Will cascade

        // Get the account id from the definition
        $accountId = $def->getAccountId();

        $this->getDatabase($accountId)->delete(
            self::ENTITY_TYPE_TABLE,
            ['entity_definition_id' => $def->getEntityDefinitionId()]
        );

        return true;
    }

    /**
     * Save a definition
     *
     * @param EntityDefinition $def The definition to save
     * @return string|bool entity id on success, false on failure
     */
    public function saveDef(EntityDefinition $def)
    {
        // Get the account id from the definition
        $accountId = $def->getAccountId();

        // Define type update
        $data = [
            "account_id" => $accountId,
            "name" => $def->getObjType(),
            "title" => $def->title,
            "revision" => $def->revision,
            "f_system" => $def->system,
            "application_id" => ($def->applicationId) ? $def->applicationId : null,
            "capped" => ($def->capped) ? $def->capped : null,
            "dacl" => ($def->getDacl()) ? json_encode(($def->getDacl()->toArray())) : null,
            "default_activity_level" => ($def->defaultActivityLevel) ? $def->defaultActivityLevel : null,
            "is_private" => $def->isPrivate,
            "store_revisions" => $def->storeRevisions,
            "recur_rules" => ($def->recurRules) ? json_encode($def->recurRules) : null,
            "inherit_dacl_ref" => ($def->inheritDaclRef) ? "'" . $def->inheritDaclRef : null,
            "parent_field" => ($def->parentField) ? $def->parentField : null,
            "uname_settings" => ($def->unameSettings) ? $def->unameSettings : null,
            "list_title" => ($def->listTitle) ? $def->listTitle : null,
            "icon" => ($def->icon) ? $def->icon : null,
            "system_definition_hash" => ($def->systemDefinitionHash) ? $def->systemDefinitionHash : null,
            "def_data" => json_encode($def->toArray()),
        ];

        $appObjectTypeId = $def->getEntityDefinitionId();
        if ($appObjectTypeId) {
            $this->getDatabase($accountId)->update(self::ENTITY_TYPE_TABLE, $data, [
                "entity_definition_id" => $appObjectTypeId,
                "account_id" => $accountId,
            ]);
        } else {
            // Create new uuid
            $data["entity_definition_id"] = Uuid::uuid4()->toString();
            $this->getDatabase($accountId)->insert(
                self::ENTITY_TYPE_TABLE,
                $data
            );

            $def->setEntityDefinitionId($data["entity_definition_id"]);
        }

        return true;

        // Check to see if this dynamic object has yet to be initilized
        // $this->createObjectTable($def->getObjType(), $def->getEntityDefinitionId());

        // // Save and create fields
        // $this->saveFields($def);

        // // Associate with applicaiton if set
        // if ($def->applicationId) {
        //     $this->associateWithApp($def, $def->applicationId);
        // }
    }
    // public function saveDef(EntityDefinition $def)
    // {
    //     // Define type update
    //     $data = [
    //         "name" => $def->getObjType(),
    //         "title" => $def->title,
    //         "revision" => $def->revision, // Increment revision in $def after updates are complete for initializing schema
    //         "f_system" => $def->system,
    //         "application_id" => ($def->applicationId) ? $def->applicationId : null,
    //         "capped" => ($def->capped) ? $def->capped : null,
    //         "dacl" => ($def->getDacl()) ? json_encode(($def->getDacl()->toArray())) : null,
    //         "default_activity_level" => ($def->defaultActivityLevel) ? $def->defaultActivityLevel : null,
    //         "is_private" => $def->isPrivate,
    //         "store_revisions" => $def->storeRevisions,
    //         "recur_rules" => ($def->recurRules) ? json_encode($def->recurRules) : null,
    //         "inherit_dacl_ref" => ($def->inheritDaclRef) ? "'" . $def->inheritDaclRef : null,
    //         "parent_field" => ($def->parentField) ? $def->parentField : null,
    //         "uname_settings" => ($def->unameSettings) ? $def->unameSettings : null,
    //         "list_title" => ($def->listTitle) ? $def->listTitle : null,
    //         "icon" => ($def->icon) ? $def->icon : null,
    //         "system_definition_hash" => ($def->systemDefinitionHash) ? $def->systemDefinitionHash : null
    //     ];

    //     foreach ($data as $colName => $colValue) {
    //         $data[$colName] = $colValue;
    //     }

    //     $appObjectTypeId = $def->getEntityDefinitionId();
    //     if ($appObjectTypeId) {
    //         $this->getDatabase($accountId)->update(self::ENTITY_TYPE_TABLE, $data, ['id' => $appObjectTypeId]);
    //     } else {
    //         $appObjectTypeId = $this->getDatabase($accountId)->insert(self::ENTITY_TYPE_TABLE, $data, 'id');

    //         $def->setEntityDefinitionId($appObjectTypeId);
    //     }

    //     // Check to see if this dynamic object has yet to be initilized
    //     $this->createObjectTable($def->getObjType(), $def->getEntityDefinitionId());

    //     // Save and create fields
    //     $this->saveFields($def);

    //     // Associate with applicaiton if set
    //     if ($def->applicationId) {
    //         $this->associateWithApp($def, $def->applicationId);
    //     }
    // }

    // /**
    //  * Save fields
    //  *
    //  * @param EntityDefinition $def The EntityDefinition we are saving
    //  */
    // private function saveFields(EntityDefinition $def)
    // {
    //     // We need to include the removed fields, so it will be permanently removed from the definition
    //     $fields = $def->getFields(true);

    //     $sort_order = 1;
    //     foreach ($fields as $fname => $field) {
    //         if ($field == null) {
    //             // Delete field
    //             $this->removeField($def, $fname);
    //         } else {
    //             // Update or add field
    //             $this->saveField($def, $field, $sort_order);
    //         }

    //         $sort_order++;
    //     }
    // }

    /**
     * Save a field
     *
     * @param EntityDefinition $def The EntityDefinition we are saving
     * @param Field $field The field definition to save
     * @param int $sort_order The order id of this field
     */
    // private function saveField(EntityDefinition $def, Field $field, $sort_order)
    // {
    //     $fname = $field->name;

    //     $sql = "select id, use_when from app_object_type_fields where name=:name and type_id=:type_id";
    //     $result = $this->getDatabase($accountId)->query($sql, ['name' => $fname, 'type_id' => $def->getEntityDefinitionId()]);
    //     if ($result->rowCount()) {
    //         $row = $result->fetch();
    //         $fid = $row["id"];
    //         $field->id = $fid;

    //         $updateFields = [];

    //         $updateFields["name"] = $fname;
    //         $updateFields["title"] = $field->title;
    //         $updateFields["type"] = $field->type;
    //         $updateFields["subtype"] = $field->subtype;

    //         if (!empty($field->fkeyTable['key'])) {
    //             $updateFields["fkey_table_key"] = $field->fkeyTable['key'];
    //         }

    //         if (!empty($field->fkeyTable['title'])) {
    //             $updateFields["fkey_table_title"] = $field->fkeyTable['title'];
    //         }

    //         if (!empty($field->fkeyTable['parent'])) {
    //             $updateFields["parent_field"] = $field->fkeyTable['parent'];
    //         }

    //         $updateFields["sort_order"] = $sort_order;
    //         $updateFields["autocreate"] = $field->autocreate;

    //         if ($field->autocreatebase) {
    //             $updateFields["autocreatebase"] = $field->autocreatebase;
    //         }

    //         if ($field->autocreatename) {
    //             $updateFields["autocreatename"] = $field->autocreatename;
    //         }

    //         if ($field->getUseWhen()) {
    //             $updateFields["use_when"] = $field->getUseWhen();
    //         }

    //         if ($field->mask) {
    //             $updateFields["mask"] = $field->mask;
    //         }

    //         if (!empty($field->fkeyTable['filter']) && is_array($field->fkeyTable['filter'])) {
    //             $updateFields["filter"] = serialize($field->fkeyTable['filter']);
    //         }

    //         $updateFields["f_required"] = $field->required;
    //         $updateFields["f_readonly"] = $field->readonly;
    //         $updateFields["f_system"] = $field->system;
    //         $updateFields["f_unique"] = $field->unique;

    //         $this->getDatabase($accountId)->update("app_object_type_fields", $updateFields, ['id' => $fid]);

    //         // Save default values
    //         if ($field->id && $field->default) {
    //             if (!empty($field->default['coalesce'])) {
    //                 $field->default['coalesce'] = null;
    //             }

    //             if (!empty($field->default['where'])) {
    //                 $field->default['where'] = null;
    //             }

    //             $this->getDatabase($accountId)->delete(
    //                 'app_object_field_defaults',
    //                 ['field_id' => $field->id]
    //             );

    //             $dataToInsert = [
    //                 "field_id" => $field->id,
    //                 "on_event" => $field->default['on'],
    //                 "value" => $field->default['value'],
    //                 "coalesce" => serialize($field->default['coalesce']),
    //                 "where_cond" => serialize($field->default['where'])
    //             ];
    //             $this->getDatabase($accountId)->insert(
    //                 "app_object_field_defaults",
    //                 $dataToInsert
    //             );
    //         }

    //         // Save field optional values
    //         if ($field->id && $field->optionalValues) {
    //             $this->getDatabase($accountId)->delete(
    //                 'app_object_field_options',
    //                 ['field_id' => $field->id]
    //             );

    //             foreach ($field->optionalValues as $okey => $oval) {
    //                 $dataToInsert = [
    //                     "field_id" => $field->id,
    //                     "key" => $okey,
    //                     "value" => $oval,
    //                 ];
    //                 $this->getDatabase($accountId)->insert(
    //                     "app_object_field_options",
    //                     $dataToInsert
    //                 );
    //             }
    //         }
    //     } else {
    //         $key = null;
    //         $fKeytitle = null;
    //         $fKeyParent = null;
    //         $fKeyFilter = null;
    //         $fKeyRef = null;
    //         $fKeyRefTable = null;
    //         $fKeyRefThis = null;
    //         $autocreatebase = null;
    //         $autocreatename = null;
    //         $mask = null;
    //         $useWhen = null;

    //         if (!empty($field->fkeyTable['key'])) {
    //             $key = $field->fkeyTable['key'];
    //         }

    //         if (!empty($field->fkeyTable['title'])) {
    //             $fKeytitle = $field->fkeyTable['title'];
    //         }

    //         if (!empty($field->fkeyTable['parent'])) {
    //             $fKeyParent = $field->fkeyTable['parent'];
    //         }

    //         if (!empty($field->fkeyTable['filter']) && is_array($field->fkeyTable['filter'])) {
    //             $fKeyFilter = serialize($field->fkeyTable['filter']);
    //         }

    //         if ($field->autocreatebase) {
    //             $autocreatebase = $field->autocreatebase;
    //         }

    //         if ($field->autocreatename) {
    //             $autocreatename = $field->autocreatename;
    //         }

    //         if ($field->mask) {
    //             $mask = $field->mask;
    //         }

    //         if ($field->getUseWhen()) {
    //             $useWhen = $field->getUseWhen();
    //         }

    //         $autocreate = "f";
    //         $required = "f";
    //         $readonly = "f";
    //         $unique = "f";

    //         if ($field->autocreate) {
    //             $autocreate = "t";
    //         }

    //         if ($field->required) {
    //             $required = "t";
    //         }

    //         if ($field->readonly) {
    //             $readonly = "t";
    //         }

    //         if ($field->unique) {
    //             $unique = "t";
    //         }

    //         $dataToInsert = [
    //             "type_id" => $def->getEntityDefinitionId(),
    //             "name" => $fname,
    //             "title" => $field->title,
    //             "type" => $field->type,
    //             "subtype" => $field->subtype,
    //             "fkey_table_key" => $key,
    //             "fkey_table_title" => $fKeytitle,
    //             "parent_field" => $fKeyParent,
    //             "fkey_multi_tbl" => $fKeyRefTable,
    //             "fkey_multi_this" => $fKeyRefThis,
    //             "fkey_multi_ref" => $fKeyRef,
    //             "sort_order" => $sort_order,
    //             "f_system" => $field->system,
    //             "autocreate" => $autocreate,
    //             "autocreatebase" => $autocreatebase,
    //             "autocreatename" => $autocreatename,
    //             "mask" => $mask,
    //             "f_required" => $required,
    //             "f_readonly" => $readonly,
    //             "filter" => $fKeyFilter,
    //             "use_when" => $useWhen,
    //             "f_unique" => $unique
    //         ];
    //         $fid = $this->getDatabase($accountId)->insert(
    //             "app_object_type_fields",
    //             $dataToInsert,
    //             'id'
    //         );

    //         if ($fid) {
    //             $fdefCoalesce = null;
    //             $fdefWhere = null;

    //             if (!empty($field->default['coalesce'])) {
    //                 $fdefCoalesce = $field->default['coalesce'];
    //             }

    //             if (!empty($field->default['where'])) {
    //                 $fdefWhere = $field->default['where'];
    //             }

    //             $field->id = $fid;

    //             if ($fid && $field->default) {
    //                 $dataToInsert = [
    //                     "field_id" => $fid,
    //                     "on_event" => $field->default['on'],
    //                     "value" => $field->default['value'],
    //                     "coalesce" => serialize($fdefCoalesce),
    //                     "where_cond" => serialize($fdefWhere)
    //                 ];
    //                 $this->getDatabase($accountId)->insert(
    //                     "app_object_field_defaults",
    //                     $dataToInsert
    //                 );
    //             }

    //             if ($fid && $field->optionalValues) {
    //                 foreach ($field->optionalValues as $okey => $oval) {
    //                     $dataToInsert = [
    //                         "field_id" => $fid,
    //                         "key" => $okey,
    //                         "value" => $oval,
    //                     ];
    //                     $this->getDatabase($accountId)->insert(
    //                         "app_object_field_options",
    //                         $dataToInsert
    //                     );
    //                 }
    //             }
    //         }
    //     }
    // }

    // /**
    //  * Remove a field from the schema and definition
    //  *
    //  * @param EntityDefintionn $def The EntityDefinition we are editing
    //  * @param string $fname The name of the field to delete
    //  */
    // private function removeField($def, $fname)
    // {
    //     if (!$def->getEntityDefinitionId()) {
    //         return false;
    //     }

    //     $this->getDatabase($accountId)->delete(
    //         'app_object_type_fields',
    //         ['name' => $fname, 'type_id' => $def->getEntityDefinitionId()]
    //     );
    // }


    /**
     * Object tables are created dynamically to inherit from the parent object table
     *
     * @param string $objType The type name of this table
     * @param int $typeId The unique id of the object type
     */
    private function createObjectTable($objType, $typeId)
    {
        // TODO: We no longer create tables for each object type
        return;

        /*
        // Make sure objType is in lower case
        $objType = strtolower($objType);
        $base = "objects_" . $objType;
        $tables = array("objects_" . $objType . "_act", "objects_" . $objType . "_del");

        // Make sure the table does not already exist
        if (!$this->getDatabase($accountId)->tableExists($base)) {
            // Base table for this object type
            $query = "CREATE TABLE $base () INHERITS (objects);";
            $this->getDatabase($accountId)->query($query);
        }

        // Active
        if (!$this->getDatabase($accountId)->tableExists($tables[0])) {
            $query = "CREATE TABLE " . $tables[0] . "
                        (
                            CONSTRAINT " . $tables[0] . "_pkey PRIMARY KEY (guid),
                            CHECK(entity_definition_id='" . $typeId . "' and f_deleted='f')
                        )
                        INHERITS ($base);";
            $this->getDatabase($accountId)->query($query);

            // Add index to legacy id until everyone moves to guid
            $this->getDatabase($accountId)->query("CREATE UNIQUE INDEX IF NOT EXISTS {$tables[0]}_id_idx ON {$tables[0]}(id)");
        }

        // Deleted / Archived
        if (!$this->getDatabase($accountId)->tableExists($tables[1])) {
            $query = "CREATE TABLE " . $tables[1] . "
                        (
                            CONSTRAINT " . $tables[1] . "_pkey PRIMARY KEY (guid),
                            CHECK(entity_definition_id='" . $typeId . "' and f_deleted='t')
                        )
                        INHERITS ($base);";
            $this->getDatabase($accountId)->query($query);

            // Add index to legacy id until everyone moves to guid
            $this->getDatabase($accountId)->query("CREATE UNIQUE INDEX IF NOT EXISTS {$tables[1]}_id_idx ON {$tables[1]}(id)");
        }

        // Create indexes for system columns
        foreach ($tables as $tbl) {
            if (!$this->getDatabase($accountId)->indexExists($tbl . "_uname_idx")) {
                $this->getDatabase($accountId)->query("CREATE INDEX " . $tbl . "_uname_idx
                              ON $tbl
                              USING btree (lower(uname))
                              where uname is not null;");
            }

            if (!$this->getDatabase($accountId)->indexExists($tbl . "_tsv_fulltext_idx")) {
                $this->getDatabase($accountId)->query("CREATE INDEX " . $tbl . "_tsv_fulltext_idx
                              ON $tbl
                              USING gin (tsv_fulltext)
                              where tsv_fulltext is not null;");
            }

            if (!$this->getDatabase($accountId)->indexExists($tbl . "_ts_entered_idx")) {
                $this->getDatabase($accountId)->query("CREATE INDEX " . $tbl . "_ts_entered_idx
                              ON $tbl (ts_entered);");
            }

            if (!$this->getDatabase($accountId)->indexExists($tbl . "_ts_updated_idx")) {
                $this->getDatabase($accountId)->query("CREATE INDEX " . $tbl . "_ts_updated_idx
                              ON $tbl (ts_entered);");
            }
        }
        */
    }

    /**
     * Create a dynamic index for a field in this object type
     *
     * This is primarily used in /services/ObjectDynIdx.php to build
     * dynamic indexes from usage stats.
     *
     * @param EntityDefinition $def The EntityDefinition we are saving
     * @param Field The Field to verity we have a column for
     */
    public function createFieldIndex(EntityDefinition $def, Field $field)
    {
        // TODO: We no longer do this as a standard entity defintiion rocess
        // later we will probably handle it by looking at the number of entities
        // that exist for a given object type, then creating index only on
        // indexed fields (in the entity definition)
        return true;

        /*
        if (!$field) {
            return false;
        }

        $colname = $field->name;
        $ftype = $field->type;
        $subtype = $field->subtype;
        $tableName = self::ENTITY_TABLE;

        if ($this->getDatabase($accountId)->columnExists($tableName, $colname) && $def->getEntityId()) {
            $index = ""; // set to create dynamic indexes

            switch ($ftype) {
                case 'text':
                    $index = ($subtype) ? "btree" : "gin";
                    break;
                case 'timestamp':
                case 'date':
                case 'integer':
                case 'numeric':
                case 'number':
                case 'fkey':
                case 'object':
                    $index = "btree";
                    break;

                case 'fkey_multi':
                    $type = "text"; // store json

                    //$type = "integer[]";
                    //$index = "GIN";
                    break;

                case 'object_multi':
                    $type = "text"; // store json

                    //$type = "text[]";
                    //$index = "GIN";
                    break;

                case 'bool':
                case 'boolean':
                default:
                    break;
            }

            // Create dynamic index
            if ($index) {
                // If we are using generic obj partitions then make sure _del table is updated as well
                $indexCol = $colname;

                if ($ftype == FIELD::TYPE_TEXT && $subtype) {
                    $indexCol = "lower($colname)";
                } elseif ($ftype == FIELD::TYPE_TEXT && !$subtype && $index == "gin") {
                    $indexCol = "to_tsvector('english', $colname)";
                }

                if (!$this->getDatabase($accountId)->indexExists($tableName . "_act_" . $colname . "_idx")) {
                    $this->getDatabase($accountId)->query("CREATE INDEX " . $tableName . "_act_" . $colname . "_idx
                                          ON " . $tableName . "_act
                                          USING $index
                                          (" . $indexCol . ");");
                }

                if (!$this->getDatabase($accountId)->indexExists($tableName . "_act_" . $colname . "_idx")) {
                    $this->getDatabase($accountId)->query("CREATE INDEX " . $tableName . "_del_" . $colname . "_idx
                                          ON " . $tableName . "_del
                                          USING $index
                                          (" . $indexCol . ");");
                }

                // Update indexed flag for this field
                $this->getDatabase($accountId)->update(
                    "app_object_type_fields",
                    ["f_indexed" => "t"],
                    ['type_id' => $def->getId(), "name" => $fname]
                );
            }

            return true;
        }

        return false;
        */
    }

    /**
     * Associate an object with an application
     *
     * @param EntityDefintion $def The definition to associate with an application
     * @param string $applicationId The unique id of the application we are associating with
     * @return bool true on success, false on failure
     */
    public function associateWithApp(EntityDefinition $def, $applicatoinId)
    {
        // Get the account id from the definition
        $accountId = $def->getAccountId();

        $otid = $def->getEntityDefinitionId();

        $sql = "select id from application_objects where application_id=:application_id and entity_definition_id=:entity_definition_id";
        $result = $this->getDatabase($accountId)->query($sql, ['application_id' => $applicatoinId, "entity_definition_id" => $otid]);

        if ($result->rowCount()) {
            $this->getDatabase($accountId)->insert(
                "application_objects",
                [
                    "application_id" => $applicatoinId,
                    "entity_definition_id" => $otid
                ]
            );
        }
    }

    /**
     * Get all the entity object types
     * @param string $accountId The account that owns the entity definition
     * 
     * @return array Collection of objects
     */
    public function getAllObjectTypes(string $accountId)
    {
        $sql = "select name from " . self::ENTITY_TYPE_TABLE . " where account_id=:account_id";        
        $result = $this->getDatabase($accountId)->query($sql, ['account_id' => $accountId]);

        foreach ($result->fetchAll() as $row) {
            $ret[] = $row['name'];
        }

        return $ret;
    }
}
