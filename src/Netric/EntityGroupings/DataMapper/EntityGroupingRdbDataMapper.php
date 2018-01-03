<?php
namespace Netric\EntityGroupings\DataMapper;

use Netric\Db\Relational\Exception\DatabaseQueryException;
use Netric\Db\Relational\RelationalDbInterface;
use DateTime;

/**
 * Load and save entity groupings with a relational database
 */
class EntityGroupingRdbDataMapper implements EntityGroupingDataMapperInterface
{
    /**
     * Handle to database
     *
     * @var RelationalDbInterface
     */
    private $database = null;

    /**
     * Class constructor
     * 
     * @param ServiceLocator $sl The ServiceLocator container
     * @param string $accountName The name of the ANT account that owns this data
     */
    public function __construct(\Netric\Account\Account $account)
    {
		// Clear the moved entities cache
        $this->cacheMovedEntities = array();

        $this->database = $this->account->getServiceManager()->get('Netric/Db/Relational/RelationalDb');
    }

    /**
     * Save groupings to a relational database
     * 
     * @param EntityGroupings
     * @param int $commitId The new commit id
     */
    public function saveGroupings(EntityGroupings $groupings, $commitId)
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
     * Get groopings from the datatabase
     *
     * @param string $objType The object type name
     * @param string $fieldName The field name to get grouping data for
     * @return EntityGroupings
     */
    public function getGroupings(string $objType, string $fieldName, $filters = [])
    {
        $def = $this->getAccount()->getServiceManager()->get("EntityDefinitionLoader")->get($objType);
        if (!$def)
            throw new \Exception("Entity could not be loaded");

        $field = $def->getField($fieldName);

        if ($field->type != "fkey" && $field->type != "fkey_multi")
            throw new \Exception("$objType:$fieldName:" . $field->type . " is not a grouping (fkey or fkey_multi) field!");

        $whereSql = '';
        $whereConditions = [];
        if ($field->subtype == "object_groupings") {
            $whereSql = 'object_type_id=:object_type_id and field_id=:field_id';
            $whereConditions['object_type_id'] = $def->getId();
            $whereConditions['field_id'] = $field->id;
        } 

        // Check filters to refine the results - can filter by parent object like project id for cases or tasks
        if (isset($field->fkeyTable['filter'])) {
            foreach ($field->fkeyTable['filter'] as $grouping_field => $object_field) {
                if (isset($filters[$object_field])) {
                    if ($whereSql) {
                        $whereSql .= ' AND ';
                    }

                    /*
                     * When passing the filter (last param with owner value)
                     * the key name is the name of the property in the entity, in this case
                     * email_message.owner_id and the value to query for. The entity definition
                     * for the grouping will map the entity field value to the grouping value if
                     * the names are different like - groupings.user_id=email_message.owner_id
                     */
                    $whereSql .= $grouping_field = ':' . $grouping_field;
                    $whereConditions[$grouping_field] = $filters[$object_field];
                } else if (isset($filters[$grouping_field])) {
                    // A filer can also come in as the grouping field name rather than the object
                    if ($whereSql) {
                        $whereSql .= ' AND ';
                    }
                    $whereSql .= $grouping_field = ':' . $grouping_field;
                    $whereConditions[$grouping_field] = $filters[$grouping_field];
                }
            }
        }

        // Filter results to this user of the object is private
        if ($def->isPrivate && !isset($filters["user_id"]) && !isset($filters["owner_id"])) {
            throw new \Exception("Private entity type called but grouping has no filter defined - " . $def->getObjType());
        }

        $sql = 'SELECT * FROM ' . $field->subtype;

        if ($whereSql) {
            $whereSql .= ' WHERE ' . $whereSql;
        }

        if ($this->database->columnExists($field->subtype, "sort_order")) {
            $sql .= ' ORDER BY sort_order, ' . (($field->fkeyTable['title']) ? $field->fkeyTable['title'] : $field->fkeyTable['key']);
        } else {
            $sql .= ' ORDER BY ' . (($field->fkeyTable['title']) ? $field->fkeyTable['title'] : $field->fkeyTable['key']);
        }

        // Technically, the limit of groupings is 1000 per field, but just to be safe
        $sql .= ' LIMIT 10000';

        $groupings = new EntityGroupings($objType, $fieldName, $filters);

        $result = $this->database->query($sql, $whereConditions);
        foreach ($result->fetchAll() as $row) {
            $group = new Group();
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

        if ($grp->color && $this->database->columnExists($field->subtype, "color")) {
            $tableData['color'] = $grp->color;
        }

        if ($grp->isSystem && $this->database->columnExists($field->subtype, "f_system")) {
            $tableData['f_system'] = $grp->isSystem;
        }

        if ($grp->sortOrder && $this->database->columnExists($field->subtype, "sort_order")) {
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

            if ($value && $this->database->columnExists($field->subtype, $name)) {
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
}
