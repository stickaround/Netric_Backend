<?php
namespace Netric\EntityGroupings\DataMapper;

use Netric\EntityDefinition;
use Netric\EntityDefinition\Field;
use Netric\Db\Relational\Exception\DatabaseQueryException;
use Netric\Db\Relational\RelationalDbInterface;
use Netric\EntitySync\Commit\CommitManager;
use Netric\EntitySync\EntitySync;
use Netric\EntityGroupings\EntityGroupings;
use Netric\EntityGroupings\Group;
use Netric\EntityDefinition\EntityDefinitionLoader;
use Netric\Db\Relational\RelationalDbFactory;
use Netric\EntitySync\EntitySyncFactory;
use Netric\EntitySync\Commit\CommitManagerFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\Account\Account;
use Ramsey\Uuid\Uuid;
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
     * Handle to current account we are mapping data for
     *
     * @var Account
     */
    protected $account = "";

    /**
     * Class constructor
     *
     * @param Account $account Current netric account loaded
     */
    public function __construct(Account $account)
    {
        // Clear the moved entities cache
        $this->cacheMovedEntities = [];
        $this->database = $account->getServiceManager()->get(RelationalDbFactory::class);
        $this->commitManager = $account->getServiceManager()->get(CommitManagerFactory::class);
        $this->entitySync = $account->getServiceManager()->get(EntitySyncFactory::class);
        $this->entityDefinitionLoader = $account->getServiceManager()->get(EntityDefinitionLoaderFactory::class);
        $this->account = $account;
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

            if ($this->saveGroup($def, $field, $grp, $groupings->getUserGuid())) {
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
                    EntitySync::COLL_TYPE_GROUPING,
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
     * @param string $userGuid Optional. Used to load a private groupings
     * @return EntityGroupings
     */
    public function getGroupings(string $objType, string $fieldName, string $userGuid = "") : EntityGroupings
    {
        $def = $this->entityDefinitionLoader->get($objType);
        if (!$def) {
            throw new \Exception("Entity could not be loaded");
        }

        $field = $def->getField($fieldName);

        if ($field->type != FIELD::TYPE_GROUPING && $field->type != FIELD::TYPE_GROUPING_MULTI) {
            throw new \Exception("$objType:$fieldName:" . $field->type . " is not a grouping (fkey or fkey_multi) field!");
        }

        $whereSql = "";
        $whereConditions = [];
        if ($field->subtype == "object_groupings") {
            $whereSql = "path = :path";
            
            $path = $def->getObjType() . "/" . $field->name;
            if ($userGuid) {
                $path .= "/$userGuid";
            }
            $whereConditions["path"] = $path;
        }


        $sql = "SELECT * FROM {$field->subtype}";

        if ($whereSql) {
            $sql .= " WHERE $whereSql";
        }

        $sql .= " ORDER BY ";
        if ($this->database->columnExists($field->subtype, "sort_order")) {
            $sql .= " sort_order, ";
        }

        $sql .= (($field->fkeyTable['title']) ? $field->fkeyTable['title'] : $field->fkeyTable['key']);

        // Technically, the limit of groupings is 1000 per field, but just to be safe
        $sql .= " LIMIT 10000";

        $groupings = new EntityGroupings($objType, $fieldName, $userGuid);

        $result = $this->database->query($sql, $whereConditions);
        foreach ($result->fetchAll() as $row) {
            $group = new Group();
            $group->id = $row[$field->fkeyTable['key']];
            $group->name = $row[$field->fkeyTable['title']];
            $group->isHeiarch = (isset($field->fkeyTable['parent'])) ? true : false;

            if (isset($field->fkeyTable['parent']) && isset($row[$field->fkeyTable['parent']])) {
                $group->parentId = $row[$field->fkeyTable['parent']];
            }

            $group->color = (isset($row['color'])) ? $row['color'] : "";

            if (isset($row['sort_order'])) {
                $group->sortOrder = $row['sort_order'];
            }

            $group->isSystem = (isset($row['f_system']) && $row['f_system'] == 't') ? true : false;
            $group->commitId = (isset($row['commit_id'])) ? $row['commit_id'] : 0;
            
            // Make sure the group is not marked as dirty
            $group->setDirty(false);

            $groupings->add($group);
        }

        return $groupings;
    }

    /**
     * Save a new or existing group
     *
     * @param EntityDefinition $def Entity type definition
     * @param Field $field The field we are saving a grouping for
     * @param Group $grp The grouping to save
     * @param String $userGuid Optional. userGuid is set if this grouping is private
     * @return bool true on success, false on failure
     */
    private function saveGroup($def, $field, Group $grp, string $userGuid = "")
    {
        if (!$field) {
            return false;
        }

        if ($field->type != FIELD::TYPE_GROUPING && $field->type != FIELD::TYPE_GROUPING_MULTI) {
            return false;
        }

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

        // Execute query
        if (count($tableData) == 0) {
            throw new \RuntimeException('Cannot save grouping - invalid data ' . var_export($grp, true));
        }

        if (empty($grp->id) === false) {
            // Update if existing
            $existingSql = "SELECT id FROM {$field->subtype} WHERE id = :id";
            if ($this->database->query($existingSql, ['id' => $grp->id])->rowCount() > 0) {
                $this->database->update($field->subtype, $tableData, ['id' => $grp->id]);
                return true;
            }

            // The ID was set but has not yet been saved. Add it to the table to save below.
            $tableData['id'] = strval($grp->id);
        }

        if ($field->subtype == "object_groupings") {
            // Set the guid and path if we are saving a new group
            $uuid4 = Uuid::uuid4();
            $tableData["guid"] = $uuid4->toString();

            $path = $def->getObjType() . "/" . $field->name;
            if ($userGuid) {
                $path .= "/$userGuid";
                $tableData["user_id"] = $userGuid = $this->account->getUser($userGuid)->getId();
            }

            $tableData["path"] = $path;
        }

        // Default to inserting
        $returnedId = $this->database->insert($field->subtype, $tableData);
        if (empty($grp->id)) {
            $grp->id = $returnedId;
        }

        return true;
    }
}
