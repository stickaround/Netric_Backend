<?php
namespace Netric\EntityGroupings\DataMapper;

use Netric\EntityDefinition\EntityDefinition;
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
        $commitHeadIdent = "groupings/" . $groupings->path;
        
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
     * Get object groupings based on unique path
     *
     * @param string $path The path of the object groupings that we are going to query
     * @return EntityGroupings
     */
    public function getGroupings(string $path) : EntityGroupings
    {
        $sql = "SELECT * FROM object_groupings WHERE path = :path ORDER BY sort_order, name LIMIT 10000";
        $result = $this->database->query($sql, ["path" => $path]);

        $groupings = new EntityGroupings($path);
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

        if ($field->type != FIELD::TYPE_GROUPING && $field->type != FIELD::TYPE_GROUPING_MULTI) {
            return false;
        }

        $groupData = $grp->toArray();

        if (!empty($grp->guid)) {
            // Update if existing
            $this->database->update("object_groupings", $groupData, ['guid' => $grp->guid]);
            return true;
        }

        // Additional data when creating a new group
        $grp->guid = Uuid::uuid4()->toString();
        $groupData["guid"] = $grp->guid;
        $groupData['object_type_id'] = $def->getId();
        $groupData['field_id'] = $field->id;

        $path = $def->getObjType() . "/" . $field->name;
        if ($userGuid) {
            $path .= "/$userGuid";
            $groupData["user_id"] = $this->account->getUser($userGuid)->getId();
        }

        $groupData["path"] = $path;

        // Since we are saving a new group, then we need to unset the id
        unset($groupData['id']);

        // Default to inserting
        $grp->id = $this->database->insert("object_groupings", $groupData);
        return true;
    }
}
