<?php
namespace Netric\EntityGroupings\DataMapper;

use Netric\EntityDefinition\Field;
use Netric\Db\Relational\Exception\DatabaseQueryException;
use Netric\Db\Relational\RelationalDbInterface;
use Netric\EntitySync\Commit\CommitManager;
use Netric\EntitySync\EntitySync;
use Netric\EntityGroupings\EntityGroupings;
use Netric\EntityGroupings\Group;
use Netric\EntityDefinition\EntityDefinitionLoader;
use \Netric\Account\Account;
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
     * Commit manager used to crate global commits for sync
     *
     * @var CommitManager
     */
    private $commitManager = null;

    /**
     * Sync service used to keep track of changes for synchronized devices
     *
     * @var EntitySync
     */
    private $entitySync = null;

    /**
     * Loader for getting entity definitions
     * 
     * @var EntityDefinitionLoader
     */
    private $entityDefinitionLoader = null;

    /**
     * Class constructor
     * 
     * @param Account $account Current netric account loaded
     */
    public function __construct(Account $account)
    {
		// Clear the moved entities cache
        $this->cacheMovedEntities = array();

        $this->database = $account->getServiceManager()->get('Netric/Db/Relational/RelationalDb');
        $this->commitManager = $account->getServiceManager()->get("EntitySyncCommitManager");
        $this->entitySync = $account->getServiceManager()->get("EntitySync");
        $this->entityDefinitionLoader = $account->getServiceManager()->get("EntityDefinitionLoader");
    }

    /**
     * Save groupings
     *
     * @param EntityGroupings $groupings Groupings object to save
     * @return array("changed"=>int[], "deleted"=>int[]) Log of changed groupings
     */
    public function saveGroupings(EntityGroupings $groupings) : array
    {
        // Now save
        $def = $this->entityDefinitionLoader->get($groupings->getObjType());
        if (!$def) {
            throw new \RuntimeException(
                'Could not get defition for entity type: ' . $groupings->getObjType()
            );
        }

        // Increment head commit for groupings which triggers all collections to sync
        $commitHeadIdent = "groupings/" . $groupings->getObjType() . "/";
        $commitHeadIdent .= $groupings->getFieldName() . "/";
        $commitHeadIdent .= $groupings::getFiltersHash($groupings->getFilters());	

    	/*
         * Groupings are all saved as a single collection, but only updated
         * groupings will shre a new commit id.
         */
        $nextCommit = $this->commitManager->createCommit($commitHeadIdent);
        
        // Now save
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
            $grp->setValue("commitId", $nextCommit);

            if ($this->saveGroup($def, $field, $grp)) {
                $grp->setDirty(false);
                // Log here
                $ret['changed'][$grp->id] = $lastCommitId;
            }
        }

        /* 
         * Log all deleted groupings to entity sync. It is important that
         * we do this for deletions because the item being synchronized is
         * removed. Changed items are automatically syncronized since the
         * commitId is changed (head commit ID) and sync commands look for
         * anything changed since a previous commit ID.
         */
        foreach ($ret['deleted'] as $gid => $lastCommitId) {
            if ($gid && $lastCommitId && $nextCommit) {
                $this->entitySync->setExportedStale(
                    \Netric\EntitySync\EntitySync::COLL_TYPE_GROUPING,
                    $lastCommitId,
                    $nextCommit
                );
            }
        }

        return $ret;
    }

    /**
     * Get object definition based on an object type
     *
     * @param string $objType The object type name
     * @param string $fieldName The field name to get grouping data for
     * @param array $filters Used to load a subset of groupings (like just for a specific user)
     * @return EntityGroupings
     */
    public function getGroupings(string $objType, string $fieldName, array $filters = []) : EntityGroupings
    {
        $def = $this->entityDefinitionLoader->get($objType);
        if (!$def)
            throw new \Exception("Entity could not be loaded");

        $field = $def->getField($fieldName);

        if ($field->type != FIELD::TYPE_GROUPING && $field->type != FIELD::TYPE_GROUPING_MULTI)
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
                    $whereSql .= $grouping_field . '=:' . $grouping_field;
                    $whereConditions[$grouping_field] = $filters[$object_field];
                } else if (isset($filters[$grouping_field])) {
                    // A filer can also come in as the grouping field name rather than the object
                    if ($whereSql) {
                        $whereSql .= ' AND ';
                    }
                    $whereSql .= $grouping_field . '=:' . $grouping_field;
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
            $sql .= ' WHERE ' . $whereSql;
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

            // Add all additional fields which are usually used for filters
            foreach ($row as $pname => $pval) {
                if (!$group->getValue($pname))
                    $group->setValue($pname, $pval);
            }

            // Make sure the group is not marked as dirty
            $group->setDirty(false);

            $groupings->add($group);
        }

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

        if ($field->type != FIELD::TYPE_GROUPING && $field->type != FIELD::TYPE_GROUPING_MULTI)
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

        if ($grp->commitId && $this->database->columnExists($field->subtype, "commit_id")) {
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
