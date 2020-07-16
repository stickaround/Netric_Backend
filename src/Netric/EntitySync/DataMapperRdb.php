<?php

/**
 * Relational database datamapper for synchronization library
 */

namespace Netric\EntitySync;

use Netric\EntitySync\Partner;
use Netric\EntitySync\EntitySync;
use Netric\EntitySync\Collection\CollectionInterface;
use DateTime;

class DataMapperRdb extends AbstractDataMapper implements DataMapperInterface
{

    /**
     * Save partner
     *
     * @param Partner $partner The partner that we will be saving
     * @return bool true on success, false on failure
     */
    public function savePartner(Partner $partner)
    {
        // PartnerID is a required param
        if (!$partner->getPartnerId()) {
            return $this->returnError("Partner id is a required param", __FILE__, __LINE__);
        }

        // User id is a required param
        if (!$partner->getOwnerId()) {
            return $this->returnError("Owner id is a required param", __FILE__, __LINE__);
        }

        // Save partnership info
        $data = array(
            "pid" => $partner->getPartnerId(),
            "owner_id" => $partner->getOwnerId(),
            "ts_last_sync" => $partner->getLastSync("Y-m-d H:i:s"),
        );

        if ($partner->getId()) {
            $this->database->update("object_sync_partners", $data, ["id" => $partner->getId()]);
        } else {
            $partnerId = $this->database->insert("object_sync_partners", $data, 'id');
            $partner->setId($partnerId);
        }

        // Save collections
        $this->savePartnerCollections($partner);

        return true;
    }

    /**
     * Save partner collections
     *
     * @param Partner $partner The partner that will be used to save its collections
     */
    private function savePartnerCollections(Partner $partner)
    {
        if (!$partner->getId()) {
            return $this->returnError("Cannot save collections because partner is not saved", __FILE__, __LINE__);
        }

        $collections = $partner->getCollections();
        for ($i = 0; $i < count($collections); $i++) {
            // If this collection was just added to the partner then it may not have the partner id set yet.
            if ($collections[$i]->getPartnerId() === null) {
                $collections[$i]->setPartnerId($partner->getId());
            }

            $this->saveCollection($collections[$i]);
        }

        // Get removed collections
        $removed = $partner->getRemovedCollections();
        foreach ($removed as $removeId) {
            $this->deleteCollection($removeId);
        }

        return true;
    }

    /**
     * Get a partner by unique system id
     *
     * @param string $partnerId Netric unique partner id
     * @return Partner or null if id does not exist
     */
    public function getPartnerById(string $partnerId)
    {
        return $this->getPartner($partnerId, null);
    }

    /**
     * Get a partner by the remote partner id
     *
     * @param string $partnerId Remotely provided unique ident
     * @return Partner or null if id does not exist
     */
    public function getPartnerByPartnerId(string $partnerId)
    {
        return $this->getPartner(null, $partnerId);
    }

    /**
     * Get a partner by either a netric system id or a client partner device id
     *
     * @param string $systemId System id
     * @param string $partnerId Device id
     * @return Partner or null if id does not exist
     */
    private function getPartner(string $systemId = null, string $partnerId = null)
    {
        // Make sure we have at least one id to pull from
        if (null == $systemId && null == $partnerId) {
            return null;
        }

        $sql = "SELECT id, pid, owner_id, ts_last_sync 
				  FROM object_sync_partners WHERE ";

        $params = [];

        // Add condition based on the type of id passed
        if ($systemId) {
            $sql .= "id=:id";
            $params["id"] = $systemId;
        } else {
            $sql .= "pid=:pid";
            $params["pid"] = $partnerId;
        }

        $result = $this->database->query($sql, $params);

        if ($result->rowCount()) {
            $row = $result->fetch();

            $partner = new Partner($this);
            $partner->setId($row['id']);
            $partner->setPartnerId($row['pid']);
            $partner->setOwnerId($row['owner_id']);

            if ($row['ts_last_sync']) {
                $partner->setLastSync(new DateTime($row['ts_last_sync']));
            }

            // Get collections
            $this->populatePartnerCollections($partner);

            return $partner;
        }

        // Not found
        return null;
    }

    /**
     * Delete a partner
     *
     * @param Partner $partner The partner to delete
     * @param bool $byPartnerId Option to delete by partner id which is useful for purging duplicates
     * @return bool true on success, false on failure
     */
    public function deletePartner(Partner $partner, bool $byPartnerId = false)
    {
        if ($partner->getId()) {
            $params = [];
            if ($byPartnerId) {
                $params["id"] = $partner->getId();
            } else {
                $params["pid"] = $partner->getPartnerId();
            }

            $this->database->delete("object_sync_partners", $params);
            return true;
        }

        return false;
    }

    /**
     * Populate collections array for a given partner using addCollection
     *
     * @param Partner $partner The partner that we will be loading its collections
     */
    private function populatePartnerCollections(Partner $partner)
    {
        // Make sure the partner was already loaded
        if (!$partner->getId()) {
            return $this->returnError("Cannot get collections because partner is not saved", __FILE__, __LINE__);
        }

        $sql = "SELECT * FROM object_sync_partner_collections WHERE partner_id=:partner_id";
        $result = $this->database->query($sql, ["partner_id" => $partner->getId()]);

        foreach ($result->fetchAll() as $row) {
            // Unserialize the conditions
            if ($row['conditions']) {
                $row['conditions'] = unserialize($row['conditions']);
            }

            // Construct a new collection
            if (!$this->getAccount()) {
                throw new \Exception("This DataMapper requires a reference to account!");
            }

            /*
             * Try to auto detect if we have data and no type.
             * This is only needed for legacy data since saving now requires a type.
             */
            if (!$row['type']) {
                if ($row['object_type'] && $row['field_name']) {
                    $row['type'] = EntitySync::COLL_TYPE_GROUPING;
                } elseif ($row['object_type']) {
                    $row['type'] = EntitySync::COLL_TYPE_ENTITY;
                }

                /*
                 * We do not need to auto-detect EntitySync::COLL_TYPE_ENTITYDEF
                 * since it is a new collection type and it is now impossible to save without
                 * the type since ::getType is an abstract requirement for all collections.
                 */
            }

            // Use a factory to construct the new collection
            $serviceManager = $this->getAccount()->getServiceManager();
            $collection = Collection\CollectionFactory::create($serviceManager, $row['type'], $row);

            // Add the collection to the partner object
            if ($collection) {
                $partner->addCollection($collection);
            }
        }

        return true;
    }

    /**
     * Save a collection
     *
     * @param CollectionInterface $collection A collection to save
     * @return bool true on success, false on failure
     */
    private function saveCollection(CollectionInterface $collection)
    {
        if (!$collection->getPartnerId()) {
            return $this->returnError("Cannot save collections because partner is not saved", __FILE__, __LINE__);
        }

        $data = [
            "type" => $collection->getType(),
            "partner_id" => $collection->getPartnerId(),
            "last_commit_id" => $collection->getLastCommitId(),
            "object_type" => $collection->getObjType(),
            "field_name" => $collection->getFieldName(),
            "revision" => $collection->getRevision(),
            "conditions" => serialize($collection->getConditions())
        ];

        if ($collection->getId()) {
            $this->database->update("object_sync_partner_collections", $data, ["id" => $collection->getId()]);
        } else {
            $collectionId = $this->database->insert("object_sync_partner_collections", $data, 'id');
            $collection->setId($collectionId);
        }

        return true;
    }

    /**
     * Delete a collection
     *
     * @param int $collectionId The id of the collection to delete
     * @return bool true on success, false on failure
     */
    private function deleteCollection(int $collectionId)
    {
        if (!$collectionId) {
            return $this->returnError("Collection id is a required param", __FILE__, __LINE__);
        }

        $this->database->delete("object_sync_partner_collections", ["id" => $collectionId]);
        return true;
    }

    /**
     * Mark a commit as stale for all sync collections
     *
     * @param int $colType Type from EntitySync::COLL_TYPE_*
     * @param string $lastCommitId
     * @param string $newCommitId
     */
    public function setExportedStale(int $collType, string $lastCommitId, string $newCommitId)
    {
        $data = ["new_commit_id" => $newCommitId];

        // Set previously exported commits as stale
        $this->database->update("object_sync_export", $data, [
            "collection_type" => $collType,
            "commit_id" => $lastCommitId
        ]);

        // Set previously stale commits as even more stale
        $this->database->update("object_sync_export", $data, [
            "collection_type" => $collType,
            "new_commit_id" => $lastCommitId
        ]);
    }

    /**
     * Log that a commit was exported from this collection
     *
     * @param int $colType Type from EntitySync::COLL_TYPE_*
     * @param int $collectionId The unique id of the collection we exported for
     * @param int $uniqueId Unique id of the object sent
     * @param int $commitId The commit id synchronized, if null then delete the entry
     * @return bool true on success, false on failure
     */
    public function logExported(int $collType = null, int $collectionId = null, int $uniqueId = null, int $commitId = null)
    {
        $whereParams = [
            "collection_id" => $collectionId,
            "unique_id" => $uniqueId
        ];

        $sql = "SELECT unique_id FROM object_sync_export
    				  WHERE collection_id=:collection_id
    				  	AND unique_id=:unique_id";

        $result = $this->database->query($sql, $whereParams);

        if ($result->rowCount()) {
            if ($commitId) {
                $updateData = ["commit_id" => $commitId, "new_commit_id" => null];
                $this->database->update("object_sync_export", $updateData, $whereParams);
            } else {
                $this->database->delete("object_sync_export", $whereParams);
            }
        } else {
            $insertData = array_merge([
                "collection_type" => $collType,
                "commit_id" => $commitId
            ], $whereParams);

            $this->database->insert("object_sync_export", $insertData);
        }

        return true;
    }

    /**
     * Get a list of previously exported commits that have been updated
     *
     * This is used to get a list of objects that were previously synchronized
     * but were later either moved outside the collection (no longer met conditions)
     * or deleted.
     *
     * This function will only return 1000 entries at a time so it should be called
     * repeatedly until the number of stats returned is 0 to process all the way
     * through the queue.
     *
     * NOTE: THIS MUST BE RUN AFTER GETTING NEW/CHANGED OBJECTS IN A COLLECTION.
     *  1. Get all new commits from last_commit and log the export
     *  2. Once all new commit updates were retrieved for a collection then call this
     *  3. Once this returns empty then fast-forward this collection to head
     *
     * @param int $collectionId The id of the collection we get stats for
     * @return int[] Array of stale IDs
     */
    public function getExportedStale(int $collectionId)
    {
        if (!is_numeric($collectionId)) {
            throw new \Exception("A valid $collectionId is a required param.");
        }

        $staleStats = [];

        // Get everything from the exported log that is set as stale
        $sql = "SELECT unique_id FROM object_sync_export 
    			WHERE collection_id=:collection_id
    				AND new_commit_id IS NOT NULL LIMIT 1000;";

        $result = $this->database->query($sql, ["collection_id" => $collectionId]);

        foreach ($result->fetchAll() as $row) {
            $staleStats[] = $row["unique_id"];
        }

        return $staleStats;
    }

    /**
     * Get a list of previously imported objects
     *
     * @param int $collectionId The id of the collection we get stats for
     * @throws \InvalidArgumentException If there is no collection id
     * @throws \Exception if we cannot query the database
     * @return array(array('remote_id', 'remote_revision', 'local_id', 'local_revision'))
     */
    public function getImported(int $collectionId)
    {
        if (!is_numeric($collectionId)) {
            throw new \InvalidArgumentException("A valid $collectionId is a required param.");
        }

        $importedStats = [];

        // Get everything from the exported log that is set as stale
        $sql = "SELECT unique_id, remote_revision, object_id, revision FROM object_sync_import
    			WHERE collection_id=:collection_id";

        $result = $this->database->query($sql, ["collection_id" => $collectionId]);
        foreach ($result->fetchAll() as $row) {
            $importedStats[] = [
                'remote_id' => $row['unique_id'],
                'remote_revision' => $row['remote_revision'],
                'local_id' => $row['object_id'],
                'local_revision' => $row['revision'],
            ];
        }

        return $importedStats;
    }

    /**
     * Log that a commit was exported from this collection
     *
     * @param int $collectionId The id of the collection we are logging changes to
     * @param string $remoteId The foreign unique id of the object being imported
     * @param int $remoteRevision A revision of the remote object (could be an epoch)
     * @param int $localId If imported to a local object then record the id, if null the delete
     * @param int $localRevision The revision of the local object
     * @return bool true if imported false if failure
     */
    public function logImported(
        int $collectionId,
        string $remoteId,
        int $remoteRevision = null,
        int $localId = null,
        int $localRevision = null
    ) {
        if (!$remoteId) {
            throw new \InvalidArgumentException("remoteId was not set and is required.");
        }

        $whereData = [
            "collection_id" => $collectionId,
            "unique_id" => $remoteId
        ];

        if ($localId) {
            $syncData = [
                "object_id" => $localId,
                "revision" => $localRevision,
                "remote_revision" => $remoteRevision
            ];

            $sql = "SELECT unique_id FROM object_sync_import
    				  WHERE collection_id=:collection_id
    				  	AND unique_id=:unique_id";
            $result = $this->database->query($sql, ["collection_id" => $collectionId, "unique_id" => $remoteId]);

            if ($result->rowCount()) {
                $this->database->update("object_sync_import", $syncData, $whereData);
            } else {
                $this->database->insert("object_sync_import", array_merge($syncData, $whereData));
            }
        } else {
            /*
             * If we have no localId then that means the import is no longer part of the local store
             * and has not been imported so delete the log entry.
             */
            $this->database->delete("object_sync_import", $whereData);
        }

        return true;
    }
}
