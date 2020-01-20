<?php
/**
 * Identity mapper for entity groupings
 */
namespace Netric\EntityGroupings;

use Netric\EntityGroupings\DataMapper\EntityGroupingDataMapperInterface;
use Netric\Cache\CacheInterface;

/**
 * Class to handle to loading of entity groupings
 */
class GroupingLoader
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
     * Setup IdentityMapper for loading objects
     *
     * @param EntityGroupingDataMapperInterface $dataMapper Datamapper for entity definitions
     * @param CacheInterface $cache Optional cache object
     * @return EntityDefinitionLoader
     */
    public function __construct(EntityGroupingDataMapperInterface $dataMapper, CacheInterface $cache = null)
    {
        $this->cache = $cache;
        $this->dataMapper = $dataMapper;
        return $this;
    }

    /**
     * Get the entity grouping using a unique path
     *
     * @param string $path The path of the grouping that we are going to load
     * @return Entity
     */
    public function get(string $path)
    {
        if (!$path) {
            throw new Exception('$path is a required param');
        }

        if ($this->isLoaded($path)) {
            return $this->loadedGroupings[$path];
        }

        return $this->loadGroupings($path);
    }

    /**
     * Save changes to groupings
     *
     * @param EntityGroupings $groupings
     * @return bool
     * @throws \RuntimeException if the datamapper cannot save groups for some reason
     */
    public function save(EntityGroupings $groupings)
    {
        // New DataMappers will throw an exception if they fail
        $this->dataMapper->saveGroupings($groupings);

        // If we did not encounter any exceptions, then things went well
        return true;
    }

    /**
     * Load the entity groupings using a path
     *
     * @param string $path The path of the grouping that we are going to load
     */
    private function loadGroupings(string $path)
    {
        $groupings = $this->dataMapper->getGroupings($path);
        $groupings->setDataMapper($this->dataMapper);

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
    private function isLoaded(string $path)
    {
        return isset($this->loadedGroupings[$path]);
    }

    /**
     * Clear cache
     *
     * @param string $path The path of the grouping
     */
    public function clearCache(string $path)
    {
        $this->loadedGroupings[$path] = null;
        return;
    }
}
