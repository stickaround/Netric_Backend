<?php
/**
 * Identity mapper for entity groupings
 */
namespace Netric\EntityGroupings;

use Netric\EntityGroupings\DataMapper\EntityGroupingDataMapperInterface;
use Netric\Cache\CacheInterface;

/**
 * Class to handle to loading of object definitions
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
     * Get an entity
     *
     * @param string $objType
     * @return Entity
     */
    public function get($objType, $fieldName, $filters = array())
    {
        if (!$objType || !$fieldName) {
            throw new Exception('$objType and $fieldName are required params');
        }

        if ($this->isLoaded($objType, $fieldName, $filters)) {
            return $this->loadedGroupings[$objType][$fieldName][$this->getFiltersHash($filters)];
        }

        return $this->loadGroupings($objType, $fieldName, $filters);
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
     * Get unique filters hash
     */
    private function getFiltersHash($filters = array())
    {
        return EntityGroupings::getFiltersHash($filters);
    }

    /**
     * Construct the definition class
     *
     * @param string $objType
     */
    private function loadGroupings($objType, $fieldName, $filters = array())
    {
        $groupings = $this->dataMapper->getGroupings($objType, $fieldName, $filters);
        $groupings->setDataMapper($this->dataMapper);
        // Cache the loaded definition for future requests
        $this->loadedGroupings[$objType][$fieldName][$this->getFiltersHash($filters)] = $groupings;
        //$this->cache->set($this->dataMapper->getAccount()->getId() . "/objects/" . $objType, $def->toArray());
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
