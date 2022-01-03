<?php

namespace Netric\EntityGroupings\DataMapper;

use Netric\EntityDefinition\EntityDefinition;
use Netric\EntityDefinition\Field;
use Netric\EntitySync\Commit\CommitManager;
use Netric\EntitySync\EntitySync;
use Netric\EntityGroupings\EntityGroupings;
use Netric\EntityGroupings\Group;
use Netric\EntityDefinition\EntityDefinitionLoader;
use Netric\Db\Relational\RelationalDbContainer;
use Netric\Db\Relational\RelationalDbInterface;
use Netric\WorkerMan\Worker\EntitySyncSetExportedStaleWorker;
use Netric\WorkerMan\WorkerService;
use Ramsey\Uuid\Uuid;

/**
 * Load and save entity groupings with a relational database
 */
class EntityGroupingRdbDataMapper implements EntityGroupingDataMapperInterface
{
    /**
     * Database container
     *
     * @var RelationalDbContainer
     */
    private $databaseContainer = null;

    /**
     * Commit manager used to crate global commits for sync
     *
     * @var CommitManager
     */
    private $commitManager = null;

    /**
     * Used to schedule background jobs
     *
     * @var WorkerService
     */
    private WorkerService $workerService;

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
     * Grouping table
     */
    const TABLE_GROUPINGS = 'entity_group';

    /**
     * Class constructor
     *
     * @param RelationalDbContainer $dbContainer Handles the database actions
     * @param EntityDefinitionLoader $defLoader Handles the loading of entity definition
     * @param EntityDefinitionLoader $entityDefinitionLoader Manage handles creating, getting, and working with commits
     * @param WorkerService $workerService Used to schedule background jobs
     */
    public function __construct(
        RelationalDbContainer $dbContainer,
        EntityDefinitionLoader $entityDefinitionLoader,
        CommitManager $commitManager,
        WorkerService $workerService
    ) {
        $this->databaseContainer = $dbContainer;
        $this->entityDefinitionLoader = $entityDefinitionLoader;
        $this->commitManager = $commitManager;
        $this->workerService = $workerService;

        // Clear the moved entities cache
        $this->cacheMovedEntities = [];
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
     * Save groupings
     *
     * @param EntityGroupings $groupings Groupings object to save
     *
     * @return array("changed"=>int[], "deleted"=>int[]) Log of changed groupings
     */
    public function saveGroupings(EntityGroupings $groupings): array
    {
        $accountId = $groupings->getAccountId();

        // Now save
        $def = $this->entityDefinitionLoader->get($groupings->getObjType(), $accountId);
        if (!$def) {
            throw new \RuntimeException(
                'Could not get definition for entity type: ' . $groupings->getObjType()
            );
        }

        // Increment head commit for groupings which triggers all collections to sync
        $commitHeadIdent = "groupings/" . $groupings->path;

        /*
         * Groupings are all saved as a single collection, but only updated
         * groupings will shre a new commit id.
         */
        $nextCommit = $this->commitManager->createCommit($commitHeadIdent);

        // Now save
        $field = $def->getField($groupings->getFieldName());
        $ret = ["deleted" => [], "changed" => []];

        $toDelete = $groupings->getDeleted();
        foreach ($toDelete as $grp) {
            $this->getDatabase($accountId)->query(
                'DELETE FROM ' . self::TABLE_GROUPINGS . ' WHERE group_id=:group_id',
                ['group_id' => $grp->getGroupId()]
            );

            // Log here
            $ret['deleted'][$grp->getGroupId()] = $grp->getCommitId();
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
                $ret['changed'][$grp->getGroupId()] = $lastCommitId;
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
                $this->workerService->doWorkBackground(EntitySyncSetExportedStaleWorker::class, [
                    'account_id' => $accountId,
                    'collection_type' => EntitySync::COLL_TYPE_GROUPING,
                    'last_commit_id' => $lastCommitId,
                    'new_commit_id' => $nextCommit
                ]);
            }
        }

        return $ret;
    }

    /**
     * Get object groupings based on unique path
     *
     * @param string $path The path of the object groupings that we are going to query
     * @param string $accountId The account that owns the groupings that we are about to save
     *
     * @return EntityGroupings
     */
    public function getGroupingsByPath(string $path, string $accountId): EntityGroupings
    {
        $sql = 'SELECT * FROM ' . self::TABLE_GROUPINGS . ' WHERE path = :path ORDER BY sort_order, name LIMIT 10000';
        $result = $this->getDatabase($accountId)->query($sql, ["path" => $path]);

        $groupings = new EntityGroupings($path, $accountId);
        foreach ($result->fetchAll() as $row) {
            $group = new Group();
            $group->fromArray($row);

            // Make sure the group is not marked as dirty
            $group->setDirty(false);
            $groupings->add($group);
        }

        return $groupings;
    }

    /**
     * Function that will get the groupings using the entity definition
     *
     * @param EntityDefinition $definition The definition that we will use to filter the object groupings
     * @param string $fieldName The name of the field of this grouping
     *
     * @return EntityGroupings
     */
    public function getGroupings($definition, $fieldName): EntityGroupings
    {
        // Get the account id from the definition
        $accountId = $definition->getAccountId();

        $sql = 'SELECT * FROM ' . self::TABLE_GROUPINGS . ' WHERE entity_definition_id = :definition_id ORDER BY sort_order, name LIMIT 10000';
        $result = $this->getDatabase($accountId)->query($sql, ["definition_id" => $definition->getEntityDefinitionId()]);

        $groupings = new EntityGroupings("{$definition->getObjType()}/$fieldName", $accountId);
        foreach ($result->fetchAll() as $row) {
            $group = new Group();
            $group->fromArray($row);

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
    private function saveGroup(EntityDefinition $def, Field $field, Group $grp, string $userGuid = "")
    {
        if (!$field) {
            return false;
        }

        if ($field->type != Field::TYPE_GROUPING && $field->type != Field::TYPE_GROUPING_MULTI) {
            return false;
        }

        // Get the account id from the definition
        $accountId = $def->getAccountId();

        $groupData = $grp->toArray();

        if (!empty($grp->getGroupId())) {
            // Update if existing
            $this->getDatabase($accountId)->update(self::TABLE_GROUPINGS, $groupData, ['group_id' => $grp->getGroupId()]);
            return true;
        }

        // Additional data when creating a new group
        $grp->setGroupId(Uuid::uuid4()->toString());
        $groupData["group_id"] = $grp->getGroupId();
        $groupData['entity_definition_id'] = $def->getEntityDefinitionId();
        $groupData['account_id'] = $accountId;

        $path = $def->getObjType() . "/" . $field->name;
        if ($userGuid) {
            $path .= "/$userGuid";
            $groupData["user_id"] = $userGuid;
        }

        $groupData["path"] = $path;

        // Default to inserting
        $this->getDatabase($accountId)->insert(self::TABLE_GROUPINGS, $groupData);
        return true;
    }
}
