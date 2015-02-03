<?php
/**
 * Geneic object sync class
 *
 * The main idea is that this class should return a list of entities
 * that have been added, changed or deleted since the last sync.
 *
 * @category Netric
 * @package EntitySync
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright Copyright (c) 2003-2015 Aereus Corporation (http://www.aereus.com)
 */

namespace Netric\EntitySync;

/**
 * Sync class
 */
class EntitySync
{
    /**
     * Current sync partner instance
     */
    private $partner = null;

    /**
     * If set then skip over a specific collection
     *
     * @var int
     */
    public $ignoreCollection = null;

    /**
     * DataMapper for persistent storage
     *
     * @var DataMapperInterface
     */
    private $dataMapper = null;

    /**
     * Constructor
     *
     * @param \Netric\EntitySync\DataMapperInterface $dm
     */
    public function __construct(DataMapperInterface $dm) 
    {
            $this->dataMapper = $dm;
    }

    /**
     * Get device
     *
     * @param string $devid The device id to query stats for
     */
    public function getPartner($pid)
    {
        if (!$pid)
        {
            throw new Exception("Partner id is required");
        }

        // First get cached partners because we do not want to load them twice
        if ($this->partner)
        {
                if ($this->partner->partnerId != $pid)
                {
                        $this->partner = null; // Reset
                }
        }
        else
        {
                // Load the partner from the database
                $this->partner = $this->dataMapper->getPartner($pid);
        }

        return $this->partner;		
    }

    /**
     * Send object grouping/field change
     *
     * Update stat partnership collections if any match the given field and value
     *
     * @param string $objType The type of entity we are working with
     * @param string $fieldName The name of the grouping field that was changed
     * @param int $fieldVal The id of the grouping that was changed
     * @param char $action The action taken: 'c' = changed, 'd' = deleted
     * @return int[] IDs of collections that were updated with the object
     */
    public function updateGroupingStat($objType, $fieldName, $fieldVal, $action='c')
    {
        $ret = array();
        $field = $this->obj->def->getField($fieldName);

        if (!$field)
                return false;

        // Get all collections that match the conditions
        $partnerships = $this->dataMapper->getListeningPartners($fieldName);
        foreach ($partnerships as $partner)
        {
            $collections = $partner->getGroupingCollections($this->obj->object_type, $fieldName);
            foreach ($collections as $coll)
            {
                $coll->updateGroupingStat($fieldVal, $action);
                $ret[] = $coll->id;
            }
        }

        return $ret;
    }
}
