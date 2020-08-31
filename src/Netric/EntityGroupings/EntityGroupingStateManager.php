<?php

namespace Netric\EntityGroupings;

use Netric\EntityGroupings\DataMapper\EntityGroupingDataMapperInterface;
use Netric\Cache\CacheInterface;
use Netric\EntitySync\Commit\CommitManager;
use Netric\EntitySync\EntitySync;
use InvalidArgumentException;

/**
 * Manage saving and loading groupings from DataMappers
 */
class EntityGroupingStateManager
{
    /**
     * The current data mapper we are using for this object
     *
     * @var EntityGroupingDataMapperInterface
     */
    protected $dataMapper = null;

    /**
     * Array of loaded groupings
     *
     * @var array
     */
    private $loadedGroupings = [];

    /**
     * Cache
     *
     * @var CacheInterface
     */
    private $cache = null;

    /**
     * Commit manager for keeping track of a chain of changes
     *
     * @var CommitManager
     */
    private $commitManager = null;

    /**
     * EntitySync service used to synchronize entities and groupings with devices
     *
     * @var EntitySync
     */
    private $entitySync = null;

    /**
     * Setup IdentityMapper for loading objects
     *
     * @param EntityGroupingDataMapperInterface $dm DataMapper for entity definitions
     * @param CommitManager $commitManager Create new unique commit IDs for tracking updates
     * @param EntitySync $entitySync Keep devices in sync when groups are updated
     * @param CacheInterface $cache Optional cache object
     */
    public function __construct(
        EntityGroupingDataMapperInterface $dm,
        CommitManager $commitManager,
        EntitySync $entitySync,
        CacheInterface $cache = null
    ) {
        $this->commitManager = $commitManager;
        $this->entitySync = $entitySync;
        $this->cache = $cache;
        $this->dataMapper = $dm;
    }

    /**
     * Get groupings for an entity field
     *
     * @param string $path The path of the grouping that we are going to load
     * @param string $accountId The account that owns the groupings that we are about to save
     * 
     * @return EntityGroupings
     */
    public function get(string $path, string $accountId)
    {
        if (!$path) {
            throw new InvalidArgumentException("$path is a required param.");
        }

        if ($this->isLoaded($path)) {
            $ret = $this->loadedGroupings[$path];
        } else {
            $ret = $this->loadGroupings($path, $accountId);
        }

        return $ret;
    }

    /**
     * Save changes to groupings
     *
     * @param EntityGroupings $groupings
     * @return mixed
     */
    public function save(EntityGroupings $groupings)
    {
        // Increment head commit for groupings which triggers all collections to sync
        $commitHeadId = "groupings/" . $groupings->path;

        // Groupings are all saved as a single collection, but only updated
        // groupings will share a new commit id.
        $nextCommit = $this->commitManager->createCommit($commitHeadId);

        // Save the grouping and get a list of groupings updated
        $updatedGroupingsData = $this->dataMapper->saveGroupings($groupings, $nextCommit);

        // Update EntitySync collection so that any subscribed devices are alerted of the change
        foreach ($updatedGroupingsData['deleted'] as $gid => $lastCommitId) {
            // Log the change in entity sync
            if ($gid && $lastCommitId && $nextCommit) {
                $this->entitySync->setExportedStale(
                    EntitySync::COLL_TYPE_GROUPING,
                    $lastCommitId,
                    $nextCommit
                );
            }
        }

        return $updatedGroupingsData;
    }

    /**
     * Load the entity groupings using a path
     *
     * @param string $path The path of the grouping that we are going to load
     * @param string $accountId The account that owns the groupings that we are about to save
     * 
     * @return EntityGroupings
     */
    private function loadGroupings(string $path, string $accountId)
    {
        $groupings = $this->dataMapper->getGroupings($path, $accountId);

        // Cache the loaded definition for future requests
        $this->loadedGroupings[$path] = $groupings;
        return $groupings;
    }


    /**
     * Check to see if the entity grouping has already been loaded
     *
     * @param string $path The path of the grouping that we are going to check if it is already cached
     * @return boolean
     */
    private function isLoaded($path)
    {
        return isset($this->loadedGroupings[$path]);
    }

    /**
     * Clear cache
     *
     * @param string $path The path of the grouping
     */
    public function clearCache($path)
    {
        $this->loadedGroupings[$path] = null;
        return;
    }
}
