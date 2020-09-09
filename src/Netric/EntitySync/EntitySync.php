<?php

declare(strict_types=1);

namespace Netric\EntitySync;

/**
 * Sync class
 */
class EntitySync
{
    /**
     * Collection types
     */
    const COLL_TYPE_ENTITY = 1;
    const COLL_TYPE_GROUPING = 2;
    const COLL_TYPE_ENTITYDEF = 3;

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
     * @param DataMapperInterface $dm
     */
    public function __construct(DataMapperInterface $dm)
    {
        $this->dataMapper = $dm;
    }

    /**
     * Get device
     *
     * @param string $devid The device id to query stats for
     * @param string $accountId The account that we will use to get active database handle
     * 
     * @throws \Exception if no partner id is defined
     * @return Partner
     */
    public function getPartner(string $pid, string $accountId)
    {
        if (!$pid) {
            throw new \Exception("Partner id is required");
        }

        // First get cached partners because we do not want to load them twice
        if ($this->partner) {
            if ($this->partner->getRemotePartnerId() == $pid) {
                return $this->partner;
            }
        }

        // Load the partner from the database
        $this->partner = $this->dataMapper->getPartnerByRemoteId($pid, $accountId);

        return $this->partner;
    }

    /**
     * Create a new partner
     *
     * @param string $pid The unique partner id
     * @param string $ownerId The unique id of the owning user
     * @param string $accountId The account that owns the Partner that we are about to save
     * 
     * @return Partner
     */
    public function createPartner(string $pid, string $ownerId, string $accountId)
    {
        $partner = new Partner($this->dataMapper);
        $partner->setRemotePartnerId($pid);
        $partner->setOwnerId($ownerId);
        $this->dataMapper->savePartner($partner, $accountId);
        return $partner;
    }

    /**
     * Save a partner
     *
     * @param Partner $partner Will set the id if new partner
     * @param string $accountId The account that owns the Partner that we are about to save
     */
    public function savePartner(Partner $partner, string $accountId)
    {
        $this->dataMapper->savePartner($partner, $accountId);
    }

    /**
     * Delete a partner
     *
     * @param Partner $partner The partner to delete
     * @param string $accountId The account that we will use to get active database handle
     * 
     */
    public function deletePartner(Partner $partner, string $accountId)
    {
        $this->dataMapper->deletePartner($partner, $accountId);
    }

    /**
     * Mark a commit as stale for all sync collections
     *
     * @param string $accountId The account that owns the collection
     * @param int $colType Type from \Netric\EntitySync::COLL_TYPE_*
     * @param string $lastCommitId
     * @param string $newCommitId
     */
    public function setExportedStale(string $accountId, string $collType, string $lastCommitId, string $newCommitId)
    {
        return $this->dataMapper->setExportedStale($accountId, $collType, $lastCommitId, $newCommitId);
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
    public function updateGroupingStat($objType, $fieldName, $fieldVal, $action = 'c')
    {
        $ret = [];
        $field = $this->obj->def->getField($fieldName);

        if (!$field) {
            return false;
        }

        // Get all collections that match the conditions
        $partnerships = $this->dataMapper->getListeningPartners($fieldName);
        foreach ($partnerships as $partner) {
            $collections = $partner->getGroupingCollections($this->obj->object_type, $fieldName);
            foreach ($collections as $coll) {
                $coll->updateGroupingStat($fieldVal, $action);
                $ret[] = $coll->getCollectionId();
            }
        }

        return $ret;
    }
}
