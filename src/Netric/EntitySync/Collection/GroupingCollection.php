<?php

declare(strict_types=1);

namespace Netric\EntitySync\Collection;

use Netric\Entity;
use Netric\EntitySync\DataMapperInterface;
use Netric\EntitySync\EntitySync;
use Netric\EntitySync\Commit;
use Netric\EntityGroupings\EntityGroupings;
use Netric\EntityGroupings\DataMapper\EntityGroupingDataMapperInterface;
use Netric\EntitySync\Commit\CommitManager;
use Netric\WorkerMan\WorkerService;
use DateTime;

/**
 * Class used to represent a sync partner or endpoint
 */
class GroupingCollection extends AbstractCollection implements CollectionInterface
{
    /**
     * DataMapper for loading groups
     *
     * @var EntityGroupingDataMapperInterface
     */
    private $groupingDataMapper = null;

    /**
     * Relational database collectionDataMapper for Entity Sync Collection
     *
     * @var CollectionDataMapperInterface
     */
    private $collectionDataMapper = null;

    /**
     * Constructor
     *
     * @param CommitManager $commitManager A manager used to keep track of commits
     * @param WorkerService $workerService Used to schedule background jobs
     * @param CollectionDataMapperInterface $collectionDataMapper Relational database dataMapper for Entity Sync Collection
     * @param EntityGroupingDataMapperInterface $groupingDataMapper Entity DataMapper for grouping
     */
    public function __construct(
        CommitManager $commitManager,
        WorkerService $workerService,
        CollectionDataMapperInterface $collectionDataMapper,
        EntityGroupingDataMapperInterface $groupingDataMapper
        
    ) {
        $this->groupingDataMapper = $groupingDataMapper;
        $this->collectionDataMapper = $collectionDataMapper;

        // Pass datamapper to parent
        parent::__construct($commitManager, $workerService, $collectionDataMapper);
    }

    /**
     * Get a stats list of what has changed locally since the last sync
     *     
     * @param bool $autoFastForward If true (default) then fast-forward collection commit_id on return
     * @param \DateTime $limitUpdatesAfter If set, only pull updates after a specific date
     * @return array of associative array [
     *      [
     *          "id", // Unique id of local object
     *          "action", // 'change'|'delete',
     *          "commit_id" // Incremental id of the commits - global revision
     *      ]
     *  ]
     */
    public function getExportChanged(
        $autoFastForward = true,
        DateTime $limitUpdatesAfter = null        
    ) {
        if (!$this->getObjType()) {
            throw new \InvalidArgumentException("Object type not set! Cannot export changes.");
        }

        if (!$this->getFieldName()) {
            throw new \InvalidArgumentException("Field name is not set! Cannot export changes.");
        }

        // Set return array
        $retStats = [];

        // Get the current commit for this collection
        $lastCollectionCommit = $this->getLastCommitId();
        if ($this->isBehindHead()) {
            $imports = [];

            // Get previously imported so we do not try to export a recent import
            if ($this->getCollectionId()) {
                $imports = $this->collectionDataMapper->getImported($this->getCollectionId(), $this->getAccountId());
            }

            // Get groupings
            $filters = $this->getFiltersFromConditions();
            $groupings = $this->groupingDataMapper->getGroupings($this->getObjType() . "/" . $this->getFieldName(), $this->getAccountId());

            // Loop through each change
            $grps = $groupings->getAll();
            for ($i = 0; $i < count($grps); $i++) {
                $grp = $grps[$i];

                if ($grp->getCommitId() > $lastCollectionCommit || !$grp->getCommitId()) {
                    // First make sure we didn't just import this
                    $skipStat = false;
                    foreach ($imports as $imported) {
                        if ($imported['local_id'] == $grp->getGroupId()
                            && $imported['local_revision'] == $grp->getCommitId()
                        ) {
                            // Skip over this export because we just imported it
                            $skipStat = true;
                            break;
                        }
                    }

                    if (!$skipStat) {
                        $retStats[] = [
                            "id" => $grp->getGroupId(),
                            "action" => 'change',
                            "commit_id" => $grp->commitId
                        ];
                    }

                    if (($autoFastForward && $grp->commitId) || $skipStat) {
                        // Fast-forward $lastCommitId to last commit_id sent
                        $this->setLastCommitId($grp->commitId);

                        // Save to exported log
                        $logRet = $this->logExported(
                            $grp->getGroupId(),
                            $grp->getCommitId()
                        );
                    }
                }
            }

            /*
             * Deleted groupings are marked after bing deleted by there is no reference
             * so it will be in the stale log.
             */
            $staleStats = $this->getExportedStale();
            if ($autoFastForward) {
                foreach ($staleStats as $stale) {
                    // Save to exported log with no commit deletes the export
                    $logRet = $this->logExported($stale['id'], null);
                }
            }
            $retStats = array_merge($retStats, $staleStats);
        }

        return $retStats;
    }

    /**
     * Get a collection type id
     *
     * @return int Type from \Netric\EntitySync::COLL_TYPE_*
     */
    public function getType()
    {
        return EntitySync::COLL_TYPE_GROUPING;
    }

    /**
     * Fast forward this collection to current head which resets it to only get future changes
     */
    public function fastForwardToHead()
    {
        $headCommitId = $this->getCollectionTypeHeadCommit();

        if ($headCommitId) {
            $this->setLastCommitId($headCommitId);
        }
    }

    /**
     * Load collection data from an associative array
     *
     * @param array $data
     */
    public function fromArray($data)
    {
        if ($data['entity_sync_collection_id']) {
            $this->setCollectionId($data['entity_sync_collection_id']);
        }
        
        if ($data['object_type']) {
            $this->setObjType($data['object_type']);
        }

        if ($data['field_id']) {
            $this->setFieldId($data['field_id']);
        }

        if ($data['field_name']) {
            $this->setFieldName($data['field_name']);
        }

        if ($data['ts_last_sync']) {
            $this->setLastSync(new DateTime($data['ts_last_sync']));
        }

        if ($data['conditions']) {
            $this->setConditions($data['conditions']);
        }

        if ($data['revision']) {
            $this->setRevision($data['revision']);
        }

        if ($data['last_commit_id']) {
            $this->setLastCommitId($data['last_commit_id']);
        }
    }

    /**
     * Convert collection conditions to simpler groupings filter which only supports equals
     *
     * @return array
     */
    private function getFiltersFromConditions()
    {
        $filters = [];
        $conditions = $this->getConditions();
        foreach ($conditions as $cond) {
            if ($cond['blogic'] == 'and' && $cond['operator'] == 'id_equal') {
                $filters[$cond['field']] = $cond['condValue'];
            }
        }
        return $filters;
    }

    /**
     * Construct unique commit identifier for this collection
     *
     * @return string
     */
    private function getCommitHeadIdent()
    {
        // TODO: if private then add the user_id as a filter field
        return "groupings/" . $this->getObjType() . "/" . $this->getFieldName();
    }

    /**
     * Get the head commit for a given collection type
     *
     * @return string The last commit id for the type of data we are watching
     */
    protected function getCollectionTypeHeadCommit()
    {
        return $this->commitManager->getHeadCommit($this->getCommitHeadIdent());
    }
}
