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
    private $loadedGroupings = array();

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
     * @param string $objType
     * @param string $fieldName A grouping (fkey|fkey_multi) field
     * @param array $filters Optional key-value array of filters to match
     * @return EntityGroupings
     */
    public function get(string $objType, string $fieldName, array $filters = [])
    {
        if (!$objType || !$fieldName) {
            throw new InvalidArgumentException('$objType and $fieldName are required params');
        }

        if ($this->isLoaded($objType, $fieldName, $filters)) {
            $ret = $this->loadedGroupings[$objType][$fieldName][$this->getFiltersHash($filters)];
        } else {
            $ret = $this->loadGroupings($objType, $fieldName, $filters);
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
        $commitHeadId = "groupings/" . $groupings->getObjType() . "/";
        $commitHeadId .= $groupings->getFieldName() . "/";
        $commitHeadId .= $groupings::getFiltersHash($groupings->getFilters());

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
     * Get unique filters hash
     *
     * @param array $filters Key-value filters to use
     * @return string Unique hash of filters
     */
    private function getFiltersHash(array $filters = [])
    {
        return EntityGroupings::getFiltersHash($filters);
    }

    /**
     * Construct the definition class
     *
     * @param string $objType
     * @param string $fieldName
     * @param array $filters
     * @return EntityGroupings
     */
    private function loadGroupings(string $objType, string $fieldName, array $filters = [])
    {
        $groupings = $this->dataMapper->getGroupings($objType, $fieldName, $filters);
        // Cache the loaded definition for future requests
        $this->loadedGroupings[$objType][$fieldName][$this->getFiltersHash($filters)] = $groupings;
        return $groupings;
    }


    /**
     * Check to see if the entity has already been loaded
     *
     * @param string $key The unique key of the loaded object
     * @return boolean
     */
    private function isLoaded($objType, $fieldName, $filters = array())
    {
        return isset($this->loadedGroupings[$objType][$fieldName][$this->getFiltersHash($filters)]);
    }

    /**
     * Clear cache
     *
     * @param string $objType The object type name
     */
    public function clearCache($objType, $fieldName, $filters = array())
    {
        $this->loadedGroupings[$objType][$fieldName][$this->getFiltersHash($filters)] = null;
        return;
    }
}
