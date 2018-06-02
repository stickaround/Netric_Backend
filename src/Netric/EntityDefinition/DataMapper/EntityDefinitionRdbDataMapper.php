<?php
namespace Netric\EntityDefinition\DataMapper;

use Netric\EntityDefinition\EntityDefinition;
use Netric\EntityDefinition\DataMapper\DataMapperAbstract;
use Netric\EntityDefinition\Field;
use Netric\Permissions\Dacl;
use Netric\Account\Account;

use Netric\Db\Relational\RelationalDbFactory;
use Netric\Db\Relational\RelationalDbInterface;
use Netric\Db\Relational\Exception\DatabaseQueryException;

/**
 * Load and save entity definition data to a relational database
 */
class EntityDefinitionRdbDataMapper extends DataMapperAbstract implements EntityDefinitionDataMapperInterface
{
    /**
     * Handle to database
     *
     * @var RelationalDbInterface
     */
    private $database = null;

    /**
     * Construct and initialize dependencies
     *
     * @param Account $account
     */
    public function __construct(Account $account)
    {
        $this->account = $account;
        $this->database = $this->account->getServiceManager()->get(RelationalDbFactory::class);
    }

    /**
     * Open an object definition by name
     *
     * @var string $objType The name of the object type
     * @var string $id The Id of the object
     * @return EntityDefinition
     */
    public function fetchByName($objType)
    {
        if (!$objType || !is_string($objType)) {
            throw new \RuntimeException('objType is a required param');
        }

        $def = new EntityDefinition($objType);

        // Get basic object definition
        // ------------------------------------------------------
        $sql = "select
			id, revision, title,
			f_system, system_definition_hash, dacl, capped,
            default_activity_level, is_private, store_revisions,
            recur_rules, inherit_dacl_ref, parent_field, uname_settings,
            list_title, icon, system_definition_hash
			from app_object_types where name=:app_object_types";
        $result = $this->database->query($sql, ['app_object_types' => $objType]);


        if ($result->rowCount()) {
            $row = $result->fetch();

            $def->title = $row["title"];
            $def->revision = (int)$row["revision"];
            $def->system = ($row["f_system"] == 1) ? true : false;
            $def->systemDefinitionHash = $row['system_definition_hash'];
            $def->setId($row["id"]);
            $def->capped = (!empty($row['capped'])) ? $row['capped'] : false;

            if (!empty($row["default_activity_level"])) {
                $def->defaultActivityLevel = $row["default_activity_level"];
            }

            if (!empty($row["is_private"])) {
                $def->isPrivate = ($row["is_private"] == 1) ? true : false;
            }

            if (!empty($row["store_revisions"])) {
                $def->storeRevisions = ($row["store_revisions"] == 1) ? true : false;
            }

            if (!empty($row["inherit_dacl_ref"])) {
                $def->inheritDaclRef = $row["inherit_dacl_ref"];
            }

            if (!empty($row["parent_field"])) {
                $def->parentField = $row["parent_field"];
            }

            if (!empty($row["uname_settings"])) {
                $def->unameSettings = $row["uname_settings"];
            }

            if (!empty($row["list_title"])) {
                $def->listTitle = $row["list_title"];
            }

            if (!empty($row["icon"])) {
                $def->icon = $row["icon"];
            }

            if (!empty($row['recur_rules'])) {
                $def->recurRules = json_decode($row['recur_rules'], true);
            }

            // Check if this definition has an access control list
            if (!empty($row['dacl'])) {
                $daclData = json_decode($row['dacl'], true);
                if ($daclData) {
                    $dacl = new Dacl($daclData);
                    $def->setDacl($dacl);
                }
            }

            // If this is the first load of this object type
            // then create the object table
            if ($def->revision <= 0) {
                $this->save($def);
            }
        }

        // Make sure this a valid definition
        if (!$def->getId()) {
            throw new \RuntimeException($this->getAccount()->getName() . ":" . $objType . " has no id in " . $this->database->getNamespace());
        }


        // Get field definitions
        // ------------------------------------------------------
        try {
            $sql = "select * from app_object_type_fields where type_id=:type_id order by title";
            $result = $this->database->query($sql, ['type_id' => $def->getId()]);
        } catch (DatabaseQueryException $ex) {
            throw new \RuntimeException(
                'Could not pull type fields from db for ' . $this->getAccount()->getName() . ":" . $objType . ":" . $ex->getMessage()
            );
        }

        foreach ($result->fetchAll() as $row) {
            $objecTable = $row['subtype'];

            // Fix the issue on user files not using the actual object table
            if ($row['subtype'] == "user_files") {
                $row['fkey_table_title'] = "name";
                $objecTable = "objects_file_act";
            }

            // Build field
            $field = new Field();
            $field->id = $row['id'];
            $field->name = $row['name'];
            $field->title = $row['title'];
            $field->type = $row['type'];
            $field->subtype = $row['subtype'];
            $field->mask = $row['mask'];
            $field->required = ($row['f_required'] == 1) ? true : false;
            $field->system = ($row['f_system'] == 1) ? true : false;
            $field->readonly = ($row['f_readonly'] == 1) ? true : false;
            $field->unique = ($row['f_unique'] == 1) ? true : false;

            if (!empty($row['use_when'])) {
                $field->setUseWhen($row['use_when']);
            }

            if ($row['type'] == FIELD::TYPE_GROUPING || $row['type'] == FIELD::TYPE_OBJECT || $row['type'] == FIELD::TYPE_GROUPING_MULTI) {
                if ($row['fkey_table_key']) {
                    $field->fkeyTable = array(
                        "key" => $row['fkey_table_key'],
                        "title" => $row['fkey_table_title'],
                        "parent" => $row['parent_field'],
                        "filter" => (!empty($row['filter']) ? unserialize($row['filter']) : null),
                    );

                    if ($row['type'] == 'fkey_multi' && $row['fkey_multi_tbl']) {
                        $field->fkeyTable['ref_table'] = array(
                            "table" => $row['fkey_multi_tbl'],
                            "this" => $row['fkey_multi_this'],
                            "ref" => $row['fkey_multi_ref']
                        );
                    }
                }

                // Autocreate
                $field->autocreate = ($row['autocreate'] == 1) ? true : false;
                $field->autocreatebase = $row['autocreatebase'];
                $field->autocreatename = $row['autocreatename'];
            }

            // Check for default
            $sql = "select * from app_object_field_defaults where field_id=:field_id";
            $defaultResult = $this->database->query($sql, ['field_id' => $row['id']]);

            foreach ($defaultResult->fetchAll() as $defaultRow) {
                $default = array('on' => $defaultRow['on_event'], 'value' => $defaultRow['value']);
                if ($defaultRow['coalesce']) {
                    $default['coalesce'] = unserialize($defaultRow['coalesce']);
                }
                if ($defaultRow['where_cond']) {
                    $default['where'] = unserialize($defaultRow['where_cond']);
                }

                // Make sure that coalesce does not cause a circular reference to self
                if (!empty($default['coalesce']) && !empty($default['coalesce'])) {
                    foreach ($default['coalesce'] as $colfld) {
                        if (is_array($colfld)) {
                            foreach ($colfld as $subcolfld) {
                                if ($subcolfld == $row['name']) {
                                    $default = null;
                                    break;
                                }
                            }
                        } elseif ($colfld == $row['name']) {
                            $default = null;
                            break;
                        }
                    }
                }

                $field->default = $default;
            }

            // Check for optional vals (drop-down)
            $sql = "select * from app_object_field_options where field_id=:field_id";
            $optionalResult = $this->database->query($sql, ['field_id' => $row['id']]);

            foreach ($optionalResult->fetchAll() as $optionalRow) {
                if (empty($this->fields[$row['name']]['optional_values'])) {
                    $this->fields[$row['name']]['optional_values'] = [];
                }

                if (empty($optionalRow['key'])) {
                    $optionalRow['key'] = $optionalRow['value'];
                }

                if (empty($field->optionalValues)) {
                    $field->optionalValues = [];
                }

                $field->optionalValues[$optionalRow['key']] = $optionalRow['value'];
            }

            /*
             * Check to see if optional values are in a custom table rather than the generic
             * app_object_field_options table. We are trying to move everything over to the new
             * generic table but it will take some time.
             */
            // DEPRECATED - All tables are now using the generic app_object_field_options table
            /*if ($row['type'] === "fkey" && !empty($row['subtype'])) {
                $resultBackComp = $dbh->query("select * from {$row['subtype']}");
                for ($index = 0; $index < $dbh->getNumRows($resultBackComp); $index++) {
                    $rowOptionalValue = $dbh->getRow($resultBackComp, $index);
                    if (!isset($this->fields[$row['name']]['optional_values']))
                        $this->fields[$row['name']]['optional_values'] = array();

                    if (!$field->optionalValues)
                        $field->optionalValues = array();

                    $field->optionalValues[$rowOptionalValue['name']] = $rowOptionalValue['name'];
                }
            }*/

            $def->addField($field);
        }

        return $def;
    }

    /**
     * Delete object definition
     *
     * @param EntityDefinition $def The definition to delete
     * @return bool true on success, false on failure
     */
    public function deleteDef(EntityDefinition $def)
    {
        // System objects cannot be deleted
        if ($def->system) {
            return false;
        }

        // Only delete existing types of course
        if (!$def->getId()) {
            return false;
        }

        // Delete object type entries from the database
        $this->database->delete(
            'app_object_type_fields',
            ['type_id' => $def->getId()]
        ); // Will cascade

        $this->database->delete(
            'app_object_types',
            ['id' => $def->getId()]
        );

        // Leave object table, it's partitioned and won't hurt anything for now
        // Later we may want a cleanup routine - Sky Stebnicki

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
        // Define type update
        $data = [
            "name" => $def->getObjType(),
            "title" => $def->title,
            "revision" => $def->revision, // Increment revision in $def after updates are complete for initializing schema
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
            "system_definition_hash" => ($def->systemDefinitionHash) ? $def->systemDefinitionHash : null
        ];

        foreach ($data as $colName => $colValue) {
            $data[$colName] = $colValue;
        }

        $appObjectTypeId = $def->getId();
        if ($appObjectTypeId) {
            $this->database->update("app_object_types", $data, ['id' => $appObjectTypeId]);
        } else {
            $appObjectTypeId = $this->database->insert("app_object_types", $data);

            $def->setId($appObjectTypeId);
        }

        // Check to see if this dynamic object has yet to be initilized
        $this->createObjectTable($def->getObjType(), $def->getId());

        // Save and create fields
        $this->saveFields($def);

        // Associate with applicaiton if set
        if ($def->applicationId) {
            $this->associateWithApp($def, $def->applicationId);
        }
    }

    /**
     * Save fields
     *
     * @param EntityDefinition $def The EntityDefinition we are saving
     */
    private function saveFields(EntityDefinition $def)
    {
        // We need to include the removed fields, so it will be permanently removed from the definition
        $fields = $def->getFields(true);

        $sort_order = 1;
        foreach ($fields as $fname => $field) {
            if ($field == null) {
                // Delete field
                $this->removeField($def, $fname);
            } else {
                // Update or add field
                $this->saveField($def, $field, $sort_order);
            }

            $sort_order++;
        }
    }

    /**
     * Save a field
     *
     * @param EntityDefinition $def The EntityDefinition we are saving
     * @param Field $field The field definition to save
     * @param int $sort_order The order id of this field
     */
    private function saveField(EntityDefinition $def, Field $field, $sort_order)
    {
        $fname = $field->name;

        $sql = "select id, use_when from app_object_type_fields where name=:name and type_id=:type_id";
        $result = $this->database->query($sql, ['name' => $fname, 'type_id' => $def->getId()]);
        if ($result->rowCount()) {
            $row = $result->fetch();
            $fid = $row["id"];
            $field->id = $fid;

            $updateFields = [];

            $updateFields["name"] = $fname;
            $updateFields["title"] = $field->title;
            $updateFields["type"] = $field->type;
            $updateFields["subtype"] = $field->subtype;

            if (!empty($field->fkeyTable['key'])) {
                $updateFields["fkey_table_key"] = $field->fkeyTable['key'];
            }

            if (!empty($field->fkeyTable['title'])) {
                $updateFields["fkey_table_title"] = $field->fkeyTable['title'];
            }

            if (!empty($field->fkeyTable['parent'])) {
                $updateFields["parent_field"] = $field->fkeyTable['parent'];
            }

            if (!empty($field->fkeyTable['ref_table']['table'])) {
                $updateFields["fkey_multi_tbl"] = $field->fkeyTable['ref_table']['table'];
            }

            if (!empty($field->fkeyTable['ref_table']['this'])) {
                $updateFields["fkey_multi_this"] = $field->fkeyTable['ref_table']['this'];
            }

            if (!empty($field->fkeyTable['ref_table']['ref'])) {
                $updateFields["fkey_multi_ref"] = $field->fkeyTable['ref_table']['ref'];
            }

            $updateFields["sort_order"] = $sort_order;
            $updateFields["autocreate"] = $field->autocreate;

            if ($field->autocreatebase) {
                $updateFields["autocreatebase"] = $field->autocreatebase;
            }

            if ($field->autocreatename) {
                $updateFields["autocreatename"] = $field->autocreatename;
            }

            if ($field->getUseWhen()) {
                $updateFields["use_when"] = $field->getUseWhen();
            }

            if ($field->mask) {
                $updateFields["mask"] = $field->mask;
            }

            if (!empty($field->fkeyTable['filter']) && is_array($field->fkeyTable['filter'])) {
                $updateFields["filter"] = serialize($field->fkeyTable['filter']);
            }

            $updateFields["f_required"] = $field->required;
            $updateFields["f_readonly"] = $field->readonly;
            $updateFields["f_system"] = $field->system;
            $updateFields["f_unique"] = $field->unique;

            $this->database->update("app_object_type_fields", $updateFields, ['id' => $fid]);

            // Save default values
            if ($field->id && $field->default) {
                if (!empty($field->default['coalesce'])) {
                    $field->default['coalesce'] = null;
                }

                if (!empty($field->default['where'])) {
                    $field->default['where'] = null;
                }

                $this->database->delete(
                    'app_object_field_defaults',
                    ['field_id' => $field->id]
                );

                $dataToInsert = [
                    "field_id" => $field->id,
                    "on_event" => $field->default['on'],
                    "value" => $field->default['value'],
                    "coalesce" => serialize($field->default['coalesce']),
                    "where_cond" => serialize($field->default['where'])
                ];
                $this->database->insert(
                    "app_object_field_defaults",
                    $dataToInsert
                );
            }

            // Save field optional values
            if ($field->id && $field->optionalValues) {
                $this->database->delete(
                    'app_object_field_options',
                    ['field_id' => $field->id]
                );

                foreach ($field->optionalValues as $okey => $oval) {
                    $dataToInsert = [
                        "field_id" => $field->id,
                        "key" => $okey,
                        "value" => $oval,
                    ];
                    $this->database->insert(
                        "app_object_field_options",
                        $dataToInsert
                    );
                }
            }
        } else {
            $key = null;
            $fKeytitle = null;
            $fKeyParent = null;
            $fKeyFilter = null;
            $fKeyRef = null;
            $fKeyRefTable = null;
            $fKeyRefThis = null;
            $autocreatebase = null;
            $autocreatename = null;
            $mask = null;
            $useWhen = null;

            if (!empty($field->fkeyTable['key'])) {
                $key = $field->fkeyTable['key'];
            }

            if (!empty($field->fkeyTable['title'])) {
                $fKeytitle = $field->fkeyTable['title'];
            }

            if (!empty($field->fkeyTable['parent'])) {
                $fKeyParent = $field->fkeyTable['parent'];
            }

            if (!empty($field->fkeyTable['filter']) && is_array($field->fkeyTable['filter'])) {
                $fKeyFilter = serialize($field->fkeyTable['filter']);
            }

            if (!empty($field->fkeyTable['ref_table']['ref'])) {
                $fKeyRef = $field->fkeyTable['ref_table']['ref'];
            }

            if (!empty($field->fkeyTable['ref_table']['table'])) {
                $fKeyRefTable = $field->fkeyTable['ref_table']['table'];
            }

            if (!empty($field->fkeyTable['ref_table']['this'])) {
                $fKeyRefThis = $field->fkeyTable['ref_table']['this'];
            }

            if ($field->autocreatebase) {
                $autocreatebase = $field->autocreatebase;
            }

            if ($field->autocreatename) {
                $autocreatename = $field->autocreatename;
            }

            if ($field->mask) {
                $mask = $field->mask;
            }

            if ($field->getUseWhen()) {
                $useWhen = $field->getUseWhen();
            }

            $autocreate = "f";
            $required = "f";
            $readonly = "f";
            $unique = "f";

            if ($field->autocreate) {
                $autocreate = "t";
            }

            if ($field->required) {
                $required = "t";
            }

            if ($field->readonly) {
                $readonly = "t";
            }

            if ($field->unique) {
                $unique = "t";
            }

            $dataToInsert = [
                "type_id" => $def->getId(),
                "name" => $fname,
                "title" => $field->title,
                "type" => $field->type,
                "subtype" => $field->subtype,
                "fkey_table_key" => $key,
                "fkey_table_title" => $fKeytitle,
                "parent_field" => $fKeyParent,
                "fkey_multi_tbl" => $fKeyRefTable,
                "fkey_multi_this" => $fKeyRefThis,
                "fkey_multi_ref" => $fKeyRef,
                "sort_order" => $sort_order,
                "f_system" => $field->system,
                "autocreate" => $autocreate,
                "autocreatebase" => $autocreatebase,
                "autocreatename" => $autocreatename,
                "mask" => $mask,
                "f_required" => $required,
                "f_readonly" => $readonly,
                "filter" => $fKeyFilter,
                "use_when" => $useWhen,
                "f_unique" => $unique
            ];
            $fid = $this->database->insert(
                "app_object_type_fields",
                $dataToInsert
            );

            if ($fid) {
                $fdefCoalesce = null;
                $fdefWhere = null;

                if (!empty($field->default['coalesce'])) {
                    $fdefCoalesce = $field->default['coalesce'];
                }

                if (!empty($field->default['where'])) {
                    $fdefWhere = $field->default['where'];
                }

                $field->id = $fid;

                if ($fid && $field->default) {
                    $dataToInsert = [
                        "field_id" => $fid,
                        "on_event" => $field->default['on'],
                        "value" => $field->default['value'],
                        "coalesce" => serialize($fdefCoalesce),
                        "where_cond" => serialize($fdefWhere)
                    ];
                    $this->database->insert(
                        "app_object_field_defaults",
                        $dataToInsert
                    );
                }

                if ($fid && $field->optionalValues) {
                    foreach ($field->optionalValues as $okey => $oval) {
                        $dataToInsert = [
                            "field_id" => $fid,
                            "key" => $okey,
                            "value" => $oval,
                        ];
                        $this->database->insert(
                            "app_object_field_options",
                            $dataToInsert
                        );
                    }
                }
            }
        }

        // Make sure column exists
        $this->checkObjColumn($def, $field);
    }

    /**
     * Remove a field from the schema and definition
     *
     * @param EntityDefintionn $def The EntityDefinition we are editing
     * @param string $fname The name of the field to delete
     */
    private function removeField(&$def, $fname)
    {
        if (!$def->getId()) {
            return false;
        }

        $this->database->delete(
            'app_object_type_fields',
            ['name' => $fname, 'type_id' => $def->getId()]
        );

        $tableName = strtolower($def->getTable());
        $this->database->query("ALTER TABLE " . $tableName . " DROP COLUMN $fname;");
    }

    /**
     * Make sure column exists for a field
     *
     * @param EntityDefintionn $def The EntityDefinition we are saving
     * @param EntityDefinition_Field The Field to verity we have a column for
     * @return bool true on success, false on failure
     */
    private function checkObjColumn($def, $field)
    {
        $colname = $field->name;
        $ftype = $field->type;
        $subtype = $field->subtype;
        $tableName = strtolower($def->getTable());

        // Use different type for creating the system revision commit_id
        if ($field->name == "commit_id") {
            $fType = "bigint";
        }

        if (!$this->database->columnExists($tableName, $colname)) {
            $index = ""; // set to create dynamic indexes

            switch ($ftype) {
                case 'text':
                    if ($subtype) {
                        if (is_numeric($subtype)) {
                            $type = "character varying($subtype)";
                            $index = "btree";
                        } else {
                            // Handle special types
                            switch ($subtype) {
                                case 'email':
                                    $type = "character varying(256)";
                                    $index = "btree";
                                    break;
                                case 'zipcode':
                                    $type = "character varying(32)";
                                    $index = "btree";
                                    break;
                                default:
                                    $type = "text";
                                    $index = "gin";
                                    break;
                            }
                        }
                    } else {
                        $type = "text";
                        $index = "gin";
                    }

                    // else leave it as text
                    break;
                case 'alias':
                    $type = "character varying(128)";
                    $index = "btree";
                    break;
                case 'timestamp':
                    $type = "timestamp with time zone";
                    $index = "btree";
                    break;
                case 'date':
                    $type = "date";
                    $index = "btree";
                    break;
                case 'integer':
                    $type = "integer";
                    $index = "btree";
                    break;
                case 'bigint':
                    $type = "bigint";
                    $index = "btree";
                    break;
                case 'real': // legacy only
                case 'numeric': // If ftype is already numeric, it should set the type
                    $type = "numeric";
                    $index = "btree";
                    break;
                case 'int':
                case 'integer':
                case 'number':
                    if ($subtype) {
                        $type = $subtype;
                    } else {
                        $type = "numeric";
                    }

                    $index = "btree";
                    break;
                case 'fkey':
                    $type = "integer";
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
                    $type = "bool DEFAULT false";
                    break;

                case 'object':
                    if ($subtype) {
                        $type = "bigint";
                        $index = "btree";
                    } else {
                        $type = "character varying(512)";
                        $index = "btree";
                    }
                    break;
                case 'auto':
                    // Special type should not have a column
                    $type = '';
                    break;
                default:
                    throw new \RuntimeException(
                        'Did not know how to create column ' .
                        $tableName . ':' . $colname . ':' . $ftype
                    );
            }

            // Make sure that the column does not exist yet
            if ($type && !$this->database->columnExists($tableName, $colname)) {
                $this->database->query("ALTER TABLE " . $tableName . " ADD COLUMN $colname $type");

                // Store cached foreign key names
                if ($ftype == FIELD::TYPE_GROUPING || $ftype == FIELD::TYPE_OBJECT || $ftype == FIELD::TYPE_GROUPING_MULTI || $ftype == FIELD::TYPE_OBJECT_MULTI) {
                    $this->database->query("ALTER TABLE " . $tableName . " ADD COLUMN " . $colname . "_fval text");
                }
            }
        } else {
            // Make sure that existing foreign fields have local _fval caches
            if ($ftype == FIELD::TYPE_GROUPING || $ftype == FIELD::TYPE_OBJECT || $ftype == FIELD::TYPE_GROUPING_MULTI || $ftype == FIELD::TYPE_OBJECT_MULTI) {
                if (!$this->database->columnExists($tableName, $colname . "_fval")) {
                    $this->database->query("ALTER TABLE " . $tableName . " ADD COLUMN " . $colname . "_fval text");
                }
            }
        }

        return true;
    }

    /**
     * Object tables are created dynamically to inherit from the parent object table
     *
     * @param string $objType The type name of this table
     * @param int $typeId The unique id of the object type
     */
    private function createObjectTable($objType, $typeId)
    {
        // Make sure objType is in lower case
        $objType = strtolower($objType);
        $base = "objects_" . $objType;
        $tables = array("objects_" . $objType . "_act", "objects_" . $objType . "_del");

        // Make sure the table does not already exist
        if (!$this->database->tableExists($base)) {
            // Base table for this object type
            $query = "CREATE TABLE $base () INHERITS (objects);";
            $this->database->query($query);
        }

        // Active
        if (!$this->database->tableExists($tables[0])) {
            $query = "CREATE TABLE " . $tables[0] . "
						(
							CONSTRAINT " . $tables[0] . "_pkey PRIMARY KEY (id),
							CHECK(object_type_id='" . $typeId . "' and f_deleted='f')
						)
						INHERITS ($base);";
            $this->database->query($query);
        }

        // Deleted / Archived
        if (!$this->database->tableExists($tables[1])) {
            $query = "CREATE TABLE " . $tables[1] . "
						(
							CONSTRAINT " . $tables[1] . "_pkey PRIMARY KEY (id),
							CHECK(object_type_id='" . $typeId . "' and f_deleted='t')
						)
						INHERITS ($base);";
            $this->database->query($query);
        }

        // Create indexes for system columns
        foreach ($tables as $tbl) {
            if (!$this->database->indexExists($tbl . "_uname_idx")) {
                $this->database->query("CREATE INDEX " . $tbl . "_uname_idx
							  ON $tbl
							  USING btree (lower(uname))
							  where uname is not null;");
            }

            if (!$this->database->indexExists($tbl . "_tsv_fulltext_idx")) {
                $this->database->query("CREATE INDEX " . $tbl . "_tsv_fulltext_idx
							  ON $tbl
							  USING gin (tsv_fulltext)
							  where tsv_fulltext is not null;");
            }

            if (!$this->database->indexExists($tbl . "_ts_entered_idx")) {
                $this->database->query("CREATE INDEX " . $tbl . "_ts_entered_idx
							  ON $tbl (ts_entered);");
            }

            if (!$this->database->indexExists($tbl . "_ts_updated_idx")) {
                $this->database->query("CREATE INDEX " . $tbl . "_ts_updated_idx
							  ON $tbl (ts_entered);");
            }
        }
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
        if (!$field) {
            return false;
        }

        $colname = $field->name;
        $ftype = $field->type;
        $subtype = $field->subtype;
        $tableName = strtolower($def->getTable());

        if ($this->database->columnExists($tableName, $colname) && $def->getId()) {
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

                if (!$this->database->indexExists($tableName . "_act_" . $colname . "_idx")) {
                    $this->database->query("CREATE INDEX " . $tableName . "_act_" . $colname . "_idx
                                          ON " . $tableName . "_act
                                          USING $index
                                          (" . $indexCol . ");");
                }

                if (!$this->database->indexExists($tableName . "_act_" . $colname . "_idx")) {
                    $this->database->query("CREATE INDEX " . $tableName . "_del_" . $colname . "_idx
                                          ON " . $tableName . "_del
                                          USING $index
                                          (" . $indexCol . ");");
                }

                // Update indexed flag for this field
                $this->database->update(
                    "app_object_type_fields",
                    ["f_indexed" => "t"],
                    ['type_id' => $def->getId(), "name" => $fname]
                );
            }

            return true;
        }

        return false;
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
        $otid = $def->getId();

        $sql = "select id from application_objects where application_id=:application_id and object_type_id=:object_type_id";
        $result = $this->database->query($sql, ['application_id' => $applicatoinId, "object_type_id" => $otid]);

        if ($result->rowCount()) {
            $this->database->insert(
                "application_objects",
                [
                    "application_id" => $applicatoinId,
                    "object_type_id" => $otid
                ]
            );
        }
    }

    /**
     * Get all the entity object types
     *
     * @return array Collection of objects
     */
    public function getAllObjectTypes()
    {
        $sql = "select name from app_object_types";
        $result = $this->database->query($sql);

        foreach ($result->fetchAll() as $row) {
            $ret[] = $row['name'];
        }

        return $ret;
    }
}
