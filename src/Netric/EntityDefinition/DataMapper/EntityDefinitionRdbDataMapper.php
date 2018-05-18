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
            throw new \Exception('objType is a required param');
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
            $def->capped = ($row['capped']) ? $row['capped'] : false;

            if ($row["default_activity_level"])
                $def->defaultActivityLevel = $row["default_activity_level"];

            if (isset($row["is_private"]))
                $def->isPrivate = ($row["is_private"] == 1) ? true : false;

            if (isset($row["store_revisions"]))
                $def->storeRevisions = ($row["store_revisions"] == 1) ? true : false;

            if (isset($row["inherit_dacl_ref"]))
                $def->inheritDaclRef = $row["inherit_dacl_ref"];

            if (isset($row["parent_field"]))
                $def->parentField = $row["parent_field"];

            if (isset($row["uname_settings"]))
                $def->unameSettings = $row["uname_settings"];

            if ($row["list_title"])
                $def->listTitle = $row["list_title"];

            if ($row["icon"])
                $def->icon = $row["icon"];

            if ($row['recur_rules']) {
                $def->recurRules = json_decode($row['recur_rules'], true);
            }

            // Check if this definition has an access control list
            if ($row['dacl']) {
                $daclData = json_decode($row['dacl'], true);
                if ($daclData) {
                    $dacl = new Dacl($daclData);
                    $def->setDacl($dacl);
                }
            }

            // If this is the first load of this object type
            // then create the object table
            if ($def->revision <= 0)
                $this->save($def);
        }

        // Make sure this a valid definition
        if (!$def->getId())
            throw new \RuntimeException($this->getAccount()->getName() . ":" . $objType . " has no id in " . $this->database->getNamespace());


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
            if ($row['use_when'])
                $field->setUseWhen($row['use_when']);
            $field->required = ($row['f_required'] == 1) ? true : false;
            $field->system = ($row['f_system'] == 1) ? true : false;
            $field->readonly = ($row['f_readonly'] == 1) ? true : false;
            $field->unique = ($row['f_unique'] == 1) ? true : false;

            if ($row['type'] == "fkey" || $row['type'] == "object" || $row['type'] == "fkey_multi") {
                if ($row['fkey_table_key']) {
                    $field->fkeyTable = array(
                        "key" => $row['fkey_table_key'],
                        "title" => $row['fkey_table_title'],
                        "parent" => $row['parent_field'],
                        "filter" => (($row['filter']) ? unserialize($row['filter']) : null),
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
                if ($defaultRow['coalesce'])
                    $default['coalesce'] = unserialize($defaultRow['coalesce']);
                if ($defaultRow['where_cond'])
                    $default['where'] = unserialize($defaultRow['where_cond']);

                // Make sure that coalesce does not cause a circular reference to self
                if (isset($default['coalesce']) && $default['coalesce']) {
                    foreach ($default['coalesce'] as $colfld) {
                        if (is_array($colfld)) {
                            foreach ($colfld as $subcolfld) {
                                if ($subcolfld == $row['name']) {
                                    $default = null;
                                    break;
                                }
                            }
                        } else if ($colfld == $row['name']) {
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
                if (!isset($this->fields[$row['name']]['optional_values']))
                    $this->fields[$row['name']]['optional_values'] = [];

                if (!$optionalRow['key'])
                    $optionalRow['key'] = $optionalRow['value'];

                if (!$field->optionalValues)
                    $field->optionalValues = [];

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
        if ($def->system)
            return false;

        // Only delete existing types of course
        if (!$def->getId())
            return false;

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
            "f_system" => (($def->system) ? 't' : 'f'),
            "application_id" => ($def->applicationId) ? $def->applicationId : null,
            "capped" => ($def->capped) ? $def->capped : null,
            "dacl" => ($def->getDacl()) ? json_encode(($def->getDacl()->toArray())) : null,
            "default_activity_level" => ($def->defaultActivityLevel) ? $def->defaultActivityLevel : null,
            "is_private" => (($def->isPrivate) ? 't' : 'f'),
            "store_revisions" => (($def->storeRevisions) ? 't' : 'f'),
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
        if ($def->applicationId)
            $this->associateWithApp($def, $def->applicationId);
    }

    /**
     * Get grouping data from a path
     *
     * @param string $fieldName The field containing the grouping information
     * @param string $path The unique path of the entity to retrieve
     * @return array See getGroupingData return value for definition of grouping data entries
     */
    public function getGroupingEntryByPath($fieldName, $path)
    {
        $parts = explode("/", $path);
        $ret = null;

        // Loop through the path and get the last entry
        foreach ($parts as $grpname) {
            if ($grpname) {
                $parent = ($ret) ? $ret['id'] : "";
                $ret = $this->getGroupingEntryByName($fieldName, $grpname, $parent);
            }
        }

        return $ret;
    }

    /**
     * Get grouping path by id
     *
     * Grouping paths are constructed using the parent id. For instance Inbox/Subgroup would be constructed
     * for a group called "Subgroup" whose parent group is "Inbox"
     *
     * @param string $fieldName The field containing the grouping information
     * @param string $gid The unique id of the group to get a path for
     * @return string The full path of the heiarchy
     */
    public function getGroupingPath($fieldName, $gid)
    {
        $grp = $this->getGroupingById($fieldName, $gid);

        $path = "";

        if ($grp['parent_id'])
            $path .= $this->getGroupingPath($fieldName, $grp['parent_id']) . "/";

        $path .= $grp['title'];

        return $path;
    }

    /**
     * Get data for a grouping field (fkey)
     *
     * @param EntityDefinition $def The eneity type definition we are working with
     * @param Field $field The grouping field
     * @param array $filter Array of conditions used to slice the groupings
     * @return array of grouping in an associate array("id", "title", "viewname", "color", "system", "children"=>array)
     */
    public function getGroupingsData(EntityDefinition $def, Field $field, $filter = array())
    {
        $data = [];

        if ($field->type != FIELD::TYPE_GROUPING && $field->type != FIELD::TYPE_GROUPING_MULTI)
            return false;

        $query = "SELECT * FROM " . $field->subtype;
        $cndData = [];

        if ($field->subtype == "object_groupings") {
            $cnd = "object_type_id=:object_type_id and field_id=:field_id ";
            $cndData["object_type_id"] = $this->object_type_id;
            $cndData["field_id"] = $field->id;
        }
        else
            $cnd = "";

        // Check filters to refine the results - can filter by parent object like project id for cases or tasks
        if ($field->fkeyTable['filter']) {
            foreach ($field->fkeyTable['filter'] as $referenced_field => $object_field) {
                if (($referenced_field == "user_id" || $referenced_field == "owner_id") && $filter[$object_field])
                    $filter[$object_field] = $this->user->id;

                if ($filter[$object_field]) {
                    if ($cnd) $cnd .= " and ";

                    // Check for parent
                    $obj_rfield = $this->def->getField($object_field);
                    if ($obj_rfield->fkeyTable && $obj_rfield->fkeyTable['parent']) {
                        // Try to get the referenced table
                        if ($obj_rfield->type == FIELD::TYPE_OBJECT) {
                            $referencedDefinition = $this->fetchByName($obj_rfield->subtype);
                            $tbl = $referencedDefinition->object_table;
                        } else {
                            $tbl = $obj_rfield->subtype;
                        }

                        $root = objFldHeiarchRoot(
                            $this->database,
                            $obj_rfield->fkeyTable['key'],
                            $obj_rfield->fkeyTable['parent'],
                            $tbl,
                            $filter[$object_field]
                        );
                        if ($root && $root != $filter[$object_field]) {
                            $cnd .= " ($referenced_field=:" . $filter[$object_field] . " or $referenced_field=:" . $root . ")";
                            $cndData[$filter[$root]] = $root;
                        } else {
                            $cnd .= " $referenced_field=:" . $filter[$object_field];
                        }
                    } else {
                        $cnd .= " $referenced_field=:" . $filter[$object_field];
                    }

                    $cndData[$filter[$object_field]] = $filter[$object_field];
                }
            }
        }

        // Filter results to this user of the object is private
        if ($this->def->isPrivate && $this->user) {
            if ($this->database->columnExists($field->subtype, "owner_id")) {
                if ($cnd) $cnd .= " and ";
                $cnd .= "owner_id=:owner_id ";

                $cndData["owner_id"] = $this->user->id;
            } else if ($this->database->columnExists($field->subtype, "user_id")) {
                if ($cnd) $cnd .= " and ";
                $cnd .= "user_id='" . $this->user->id . "' ";

                $cndData["user_id"] = $this->user->id;
            }
        }

        if ($field->fkeyTable['parent']) {
            if ($parent) {
                if ($cnd) $cnd .= " and ";
                $cnd .= $field->fkeyTable['parent'] . "=:parent";

                $cndData["parent"] = $parent;
            } else {
                if ($cnd) $cnd .= " and ";
                $cnd .= $field->fkeyTable['parent'] . " is null ";
            }
        }

        if ($nameValue) {
            if ($cnd) $cnd .= " and ";

            $nameValue = strtolower($nameValue);
            $cnd .= "lower(" . $field->fkeyTable['title'] . ")=:$nameValue";

            $cndData[$nameValue] = $nameValue;
        }

        // Add conditions for advanced filtering
        if (isset($conditions) && is_array($conditions)) {
            foreach ($conditions as $cond)
                $cnd .= $cond['blogic'] . " " . $cond['field'] . " " . $cond['operator'] . " :" . $cond['condValue'] . " ";

            $cndData[$cond['condValue']] = $cond['condValue'];
        }

        if ($cnd)
            $query .= " WHERE $cnd ";

        if ($this->database->columnExists($field->subtype, "sort_order"))
            $query .= " ORDER BY sort_order, " . (($field->fkeyTable['title']) ? $field->fkeyTable['title'] : $field->fkeyTable['key']);
        else
            $query .= " ORDER BY " . (($field->fkeyTable['title']) ? $field->fkeyTable['title'] : $field->fkeyTable['key']);

        if ($limit) $query .= " LIMIT $limit";

        $result = $this->database->query($query, $cndData);

        foreach ($result->fetchAll() as $row) {
            $item = [];
            $viewname = $prefix . str_replace(" ", "_", str_replace("/", "-", $row[$field->fkeyTable['title']]));

            $item['id'] = $row[$field->fkeyTable['key']];
            $item['uname'] = $row[$field->fkeyTable['key']]; // groupings can/should have a unique-name column
            $item['title'] = $row[$field->fkeyTable['title']];
            $item['heiarch'] = ($field->fkeyTable['parent']) ? true : false;
            $item['parent_id'] = $row[$field->fkeyTable['parent']];
            $item['viewname'] = $viewname;
            $item['color'] = $row['color'];
            $item['f_closed'] = (isset($row['f_closed']) && $row['f_closed'] == 1) ? true : false;
            $item['system'] = (isset($row['f_system']) && $row['f_system'] == 1) ? true : false;

            if (isset($row['type']))
                $item['type'] = $row['type'];

            if (isset($row['mailbox']))
                $item['mailbox'] = $row['mailbox'];

            if (isset($row['sort_order']))
                $item['sort_order'] = $row['sort_order'];

            if (isset($field->fkeyTable['parent']) && $field->fkeyTable['parent'])
                $item['children'] = $this->getGroupingData($field->name, $conditions, $filter, $limit, $row[$field->fkeyTable['key']], null, $prefix . "&nbsp;&nbsp;&nbsp;");
            else
                $item['children'] = [];

            // Add all additional fields which are usually used for filters
            foreach ($row as $pname => $pval) {
                if (!isset($item[$pname]))
                    $item[$pname] = $pval;
            }

            $data[] = $item;
        }

        // Make sure that default groupings exist (if any)
        if (!$parent && sizeof($conditions) == 0) // Do not create default groupings if data is filtered
            $ret = $this->verifyDefaultGroupings($field->name, $data, $nameValue);
        else
            $ret = $data;

        return $ret;
    }

    /**
     * Insert a new entry into the table of a grouping field (fkey)
     *
     * @param string $fieldName the name of the grouping(fkey, fkey_multi) field
     * @param string $title the required title of this grouping
     * @param string $parentId the parent id to query for subvalues
     * @param bool $system If true this is a system group that cannot be deleted
     * @param array $args Optional arguments
     * @return array ("id", "title", "viewname", "color", "system", "children"=>array) of newly created grouping entry
     */
    public function addGroupingEntry($fieldName, $title, $color = "", $sortOrder = 1, $parentId = "", $system = false, $args = array())
    {
        $field = $this->def->getField($fieldName);

        if ($field->type != FIELD::TYPE_GROUPING && $field->type != FIELD::TYPE_GROUPING_MULTI)
            return false;

        if (!$field)
            return false;


        // Handle hierarchical title - relative to parent if set
        if (strpos($title, "/")) {
            $parentPath = substr($title, 0, strrpos($title, '/'));
            $pntGrp = $this->getGroupingEntryByPath($fieldName, $parentPath);
            if (!$pntGrp) // go back a level and create parent - recurrsively
            {
                $this->addGroupingEntry($fieldName, $parentPath);
                $pntGrp = $this->getGroupingEntryByPath($fieldName, $parentPath);
            }

            $parentId = $pntGrp['id'];
            $title = substr($title, strrpos($title, '/') + 1);
        }

        // Check to see if grouping with this name already exists
        if (!isset($args['no_check_existing'])) // used to limit infinite loops
        {
            $exGrp = $this->getGroupingEntryByName($fieldName, $title, $parentId);
            if (is_array($exGrp)) {
                return $exGrp;
            }
        }

        $fieldSubtypeData = [];

        if ($title && $field->fkeyTable['title'])
            $fieldSubtypeData[$field->fkeyTable['title']] = $title;

        if ($system && $this->database->columnExists($field->subtype, "f_system"))
            $fieldSubtypeData["f_system"] = "t";

        if ($color && $this->database->columnExists($field->subtype, "color"))
            $fieldSubtypeData["color"] = $color;

        if ($sortOrder && $this->database->columnExists($field->subtype, "sort_order"))
            $fieldSubtypeData["sort_order"] = $sortOrder;

        if ($parentId && $field->fkeyTable['parent'])
            $fieldSubtypeData[$field->fkeyTable['parent']] = $parentId;

        if ($field->subtype == "object_groupings") {
            $fieldSubtypeData["object_type_id"] = $this->object_type_id;
            $fieldSubtypeData["field_id"] = $field->id;
        }

        if ($this->def->isPrivate && $this->user) {
            if ($this->database->columnExists($field->subtype, "owner_id")) {
                $fieldSubtypeData["owner_id"] = $this->user->id;
            } else if ($this->database->columnExists($field->subtype, "user_id")) {
                $fieldSubtypeData["user_id"] = $this->user->id;
            }
        }

        if (isset($args['type'])) {
            $fieldSubtypeData["type"] = $args['type'];
        }

        if (isset($args['mailbox'])) {
            $fieldSubtypeData["mailbox"] = $args['mailbox'];
        }

        if (isset($args['feed_id'])) {
            $fieldSubtypeData["feed_id"] = $args['feed_id'];
        }

        // Execute query
        if (sizeof($fields) > 0) {
            $eid = $this->database->insert($field->subtype, $fieldSubtypeData);

            if ($eid) {

                $item = [];
                $item['id'] = $eid;
                $item['title'] = $title;
                $item['heiarch'] = ($field->fkeyTable['parent']) ? true : false;
                $item['parent_id'] = $parentId;
                $item['viewname'] = $title;
                $item['color'] = $color;
                $item['system'] = $system;

                if (isset($args['type']))
                    $item['type'] = $args['type'];

                if (isset($args['mailbox']))
                    $item['mailbox'] = $args['mailbox'];

                // Update sync stats
                $this->updateObjectSyncStat('c', $fieldName, $eid);

                return $item;
            }
        }

        return false;
    }

    /**
     * Get the grouping entry by id
     *
     * @param string $fieldName the name of the grouping(fkey, fkey_multi) field
     * @param int $entryId the id to delete
     * @return bool true on sucess, false on failure
     */
    public function getGroupingById($fieldName, $entryId)
    {
        $field = $this->def->getField($fieldName);

        if ($field->type != FIELD::TYPE_GROUPING && $field->type != FIELD::TYPE_GROUPING_MULTI)
            return false;

        if (!is_numeric($entryId) || !$field)
            return false;

        $ret = [];
        $sql = "select * from {$field->subtype} where id=:id";
        $result = $this->database->query($sql, ["id" => $entryId]);

        foreach ($result->fetchAll() as $row) {
            $ret = [];
            $viewname = $prefix . str_replace(" ", "_", str_replace("/", "-", $row[$field->fkeyTable['title']]));

            $ret['id'] = $row[$field->fkeyTable['key']];
            $ret['uname'] = $row[$field->fkeyTable['key']]; // groupings can/should have a unique-name column
            $ret['title'] = $row[$field->fkeyTable['title']];
            $ret['heiarch'] = ($field->fkeyTable['parent']) ? true : false;
            $ret['parent_id'] = $row[$field->fkeyTable['parent']];
            $ret['viewname'] = $viewname;
            $ret['color'] = $row['color'];
            $ret['f_closed'] = (isset($row['f_closed']) && $row['f_closed'] == 1) ? true : false;
            $ret['system'] = (isset($row['f_system']) && $row['f_system'] == 1) ? true : false;
        }

        return $ret;
    }

    /**
     * Get the grouping full path by id
     *
     * @param string $fieldName the name of the grouping(fkey, fkey_multi) field
     * @param int $entryId the id to get
     * @return string The full path delimited with '/'
     */
    public function getGroupingPathById($fieldName, $entryId)
    {
        $field = $this->def->getField($fieldName);

        if ($field->type != FIELD::TYPE_GROUPING && $field->type != FIELD::TYPE_GROUPING_MULTI)
            return false;

        if (!is_numeric($entryId) || !$field)
            return false;

        $ret = "";
        $sql = "SELECT * FROM {$field->subtype} WHERE id=:id";
        $result = $this->database->query($sql, ["id" => $entryId]);
        foreach ($result->fetchAll() as $row) {

            if ($row[$field->fkeyTable['parent']])
                $ret = $this->getGroupingPathById($fieldName, $row[$field->fkeyTable['parent']]);

            if ($ret)
                $ret .= "/";

            $ret .= $row[$field->fkeyTable['title']];
        }

        return $ret;
    }

    /**
     * Delete and entry from the table of a grouping field (fkey)
     *
     * @param string $fieldName the name of the grouping(fkey, fkey_multi) field
     * @param int $entryId the id to delete
     * @return bool true on sucess, false on failure
     */
    public function deleteGroupingEntry($fieldName, $entryId)
    {
        $field = $this->def->getField($fieldName);

        if ($field->type != FIELD::TYPE_GROUPING && $field->type != FIELD::TYPE_GROUPING_MULTI)
            return false;

        if (!is_numeric($entryId) || !$field)
            return false;

        // First delete child entries
        if ($field->fkeyTable['parent']) {
            $sql = "SELECT id FROM " . $field->subtype . " WHERE " . $field->fkeyTable['parent'] . "=:entryId";
            $result = $this->database->query($sql, ["entryId" => $entryId]);
            foreach ($result->fetchAll() as $row) {
                $this->deleteGroupingEntry($fieldName, $row["id"]);
            }
        }

        $this->database->delete(
            $field->subtype,
            ['id' => $entryId]
        );

        // Update sync stats
        $this->updateObjectSyncStat('d', $fieldName, $entryId);

        return true;
    }

    /**
     * Update an entry in the table of a grouping field (fkey)
     *
     * @param string $fieldName the name of the grouping(fkey, fkey_multi) field
     * @param int $entryId the id to delete
     * @param string $title the new name of the entry id
     * @return bool true on sucess, false on failure
     */
    public function updateGroupingEntry($fieldName, $entryId, $title = null, $color = null, $sortOrder = null, $parentId = null, $system = null)
    {
        if (!is_numeric($entryId))
            return false;

        $field = $this->def->getField($fieldName);

        if ($field->type != FIELD::TYPE_GROUPING && $field->type != FIELD::TYPE_GROUPING_MULTI)
            return false;

        $updateData = [];

        if ($title && $field->fkeyTable['title'])
            $updateData[$field->fkeyTable['title']] = $title;

        if ($color)
            $updateData["color"] = $color;

        if ($sortOrder && $this->database->columnExists($field->subtype, "sort_order"))
            $updateData["sort_order"] = $sortOrder;

        if ($parentId && $field->fkeyTable['parent'])
            $updateData[$field->fkeyTable['parent']] = $parentId;

        // Execute query
        if (sizeof($updateData) > 0) {
            $this->database->update($field->subtype, $updateData, ['id' => $entryId]);
        }

        // Update sync stats
        $this->updateObjectSyncStat('c', $fieldName, $entryId);

        return true;
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

            if (isset($field->fkeyTable['key']))
                $updateFields["fkey_table_key"] = $field->fkeyTable['key'];

            if (isset($field->fkeyTable['title']))
                $updateFields["fkey_table_title"] = $field->fkeyTable['title'];

            if (isset($field->fkeyTable['parent']))
                $updateFields["parent_field"] = $field->fkeyTable['parent'];

            if (isset($field->fkeyTable['ref_table']['table']))
                $updateFields["fkey_multi_tbl"] = $field->fkeyTable['ref_table']['table'];

            if (isset($field->fkeyTable['ref_table']['this']))
                $updateFields["fkey_multi_this"] = $field->fkeyTable['ref_table']['this'];

            if (isset($field->fkeyTable['ref_table']['ref']))
                $updateFields["fkey_multi_ref"] = $field->fkeyTable['ref_table']['ref'];

            $updateFields["sort_order"] = $sort_order;
            $updateFields["autocreate"] = (($field->autocreate) ? 't' : 'f');

            if ($field->autocreatebase)
                $updateFields["autocreatebase"] = $field->autocreatebase;

            if ($field->autocreatename)
                $updateFields["autocreatename"] = $field->autocreatename;

            if ($field->getUseWhen())
                $updateFields["use_when"] = $field->getUseWhen();

            if ($field->mask)
                $updateFields["mask"] = $field->mask;

            if (isset($field->fkeyTable['filter']) && is_array($field->fkeyTable['filter']))
                $updateFields["filter"] = serialize($field->fkeyTable['filter']);

            $updateFields["f_required"] = (($field->required) ? 't' : 'f');
            $updateFields["f_readonly"] = (($field->readonly) ? 't' : 'f');
            $updateFields["f_system"] = (($field->system) ? 't' : 'f');
            $updateFields["f_unique"] = (($field->unique) ? 't' : 'f');

            $this->database->update("app_object_type_fields", $updateFields, ['id' => $fid]);

            // Save default values
            if ($field->id && $field->default) {
                if (!isset($field->default['coalesce']))
                    $field->default['coalesce'] = null;

                if (!isset($field->default['where']))
                    $field->default['where'] = null;

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

            if (isset($field->fkeyTable['key']))
                $key = $field->fkeyTable['key'];

            if (isset($field->fkeyTable['title']))
                $fKeytitle = $field->fkeyTable['title'];

            if (isset($field->fkeyTable['parent']))
                $fKeyParent = $field->fkeyTable['parent'];

            if (isset($field->fkeyTable['filter']) && is_array($field->fkeyTable['filter']))
                $fKeyFilter = serialize($field->fkeyTable['filter']);

            if (isset($field->fkeyTable['ref_table']['ref']))
                $fKeyRef = $field->fkeyTable['ref_table']['ref'];

            if (isset($field->fkeyTable['ref_table']['table']))
                $fKeyRefTable = $field->fkeyTable['ref_table']['table'];

            if (isset($field->fkeyTable['ref_table']['this']))
                $fKeyRefThis = $field->fkeyTable['ref_table']['this'];

            if ($field->autocreatebase)
                $autocreatebase = $field->autocreatebase;

            if ($field->autocreatename)
                $autocreatename = $field->autocreatename;

            if ($field->mask)
                $mask = $field->mask;

            if ($field->getUseWhen())
                $useWhen = $field->getUseWhen();

            $autocreate = "f";
            $required = "f";
            $readonly = "f";
            $unique = "f";

            if ($field->autocreate)
                $autocreate = "t";

            if ($field->required)
                $required = "t";

            if ($field->readonly)
                $readonly = "t";

            if ($field->unique)
                $unique = "t";

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
                "f_system" => (($field->system) ? 't' : 'f'),
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

                if (isset($field->default['coalesce']))
                    $fdefCoalesce = $field->default['coalesce'];

                if (isset($field->default['where']))
                    $fdefWhere = $field->default['where'];

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
        if (!$def->getId())
            return false;

        $this->database->delete(
            'app_object_type_fields',
            ['name' => $fname, 'type_id' => $def->getId()]
        );

        $this->database->query("ALTER TABLE " . $def->getTable() . " DROP COLUMN $fname;");
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

        // Use different type for creating the system revision commit_id
        if ($field->name == "commit_id")
            $fType = "bigint";

        if (!$this->database->columnExists($def->getTable(), $colname)) {
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
                    if ($subtype)
                        $type = $subtype;
                    else
                        $type = "numeric";

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
                        $def->getTable() . ':' . $colname . ':' . $ftype
                    );
            }

            if ($type) {
                $this->database->query("ALTER TABLE " . $def->getTable() . " ADD COLUMN $colname $type");

                // Store cached foreign key names
                if ($ftype == "fkey" || $ftype == "object" || $ftype == "fkey_multi" || $ftype == "object_multi")
                    $this->database->query("ALTER TABLE " . $def->getTable() . " ADD COLUMN " . $colname . "_fval text");
            }
        } else {
            // Make sure that existing foreign fields have local _fval caches
            if ($ftype == "fkey" || $ftype == "object" || $ftype == "fkey_multi" || $ftype == "object_multi") {
                if (!$this->database->columnExists($def->getTable(), $colname . "_fval"))
                    $this->database->query("ALTER TABLE " . $def->getTable() . " ADD COLUMN " . $colname . "_fval text");
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
        if (!$field)
            return false;

        $colname = $field->name;
        $ftype = $field->type;
        $subtype = $field->subtype;

        if ($this->database->columnExists($def->getTable(), $colname) && $def->getId()) {
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

                if ($ftype == "text" && $subtype)
                    $indexCol = "lower($colname)";
                else if ($ftype == "text" && !$subtype && $index == "gin")
                    $indexCol = "to_tsvector('english', $colname)";

                if (!$this->database->indexExists($def->getTable() . "_act_" . $colname . "_idx")) {
                    $this->database->query("CREATE INDEX " . $def->getTable() . "_act_" . $colname . "_idx
                                          ON " . $def->getTable() . "_act
                                          USING $index
                                          (" . $indexCol . ");");
                }

                if (!$this->database->indexExists($def->getTable() . "_act_" . $colname . "_idx")) {
                    $this->database->query("CREATE INDEX " . $def->getTable() . "_del_" . $colname . "_idx
                                          ON " . $def->getTable() . "_del
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
