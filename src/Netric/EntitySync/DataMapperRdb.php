<?php

declare(strict_types=1);

namespace Netric\EntitySync;

use Netric\EntitySync\Partner;
use Netric\EntitySync\EntitySync;
use Netric\EntitySync\Collection\CollectionInterface;
use Netric\EntitySync\Collection\CollectionFactory;
use Netric\Db\Relational\RelationalDbContainer;
use Netric\Db\Relational\RelationalDbInterface;
use Netric\WorkerMan\Worker\EntitySyncLogImportedWorker;
use Netric\WorkerMan\Worker\EntitySyncLogExportedWorker;
use Netric\WorkerMan\Worker\EntitySyncSetExportedStaleWorker;
use Netric\WorkerMan\WorkerService;
use DateTime;

/**
 * Relational database datamapper for synchronization library
 */
class DataMapperRdb extends AbstractDataMapper implements DataMapperInterface
{
    /**
     * Database container
     *
     * @var RelationalDbContainer
     */
    private $databaseContainer = null;

    /**
     * Collection factory that will create an instance of entity/grouping collection
     *
     * @var CollectionFactory
     */
    private $collectionFactory = null;

    /**
     * Used to schedule background jobs
     *
     * @var WorkerService
     */
    private WorkerService $workerService;

    /**
     * Setup this datamapper
     *
     * @param RelationalDbContainer $databaseContainer Used to get active database connection for the right account
     * @param CollectionFactory $collectionFactory Collection factory that will create an instance of a collection
     * @param WorkerService $workerService Used to schedule background jobs
     */
    protected function setUp(
        RelationalDbContainer $dbContainer,
        WorkerService $workerService,
        CollectionFactory $collectionFactory
    ) {
        $this->databaseContainer = $dbContainer;
        $this->collectionFactory = $collectionFactory;
        $this->workerService = $workerService;
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
     * Save partner
     *
     * @param Partner $partner The partner that we will be saving
     * @param string $accountId The account that owns the Partner that we are about to save
     *
     * @return bool true on success, false on failure
     */
    public function savePartner(Partner $partner, string $accountId): bool
    {
        // PartnerID is a required param
        if (!$partner->getRemotePartnerId()) {
            return $this->returnError("Partner id is a required param", __FILE__, __LINE__);
        }

        // User id is a required param
        if (!$partner->getOwnerId()) {
            return $this->returnError("Owner id is a required param", __FILE__, __LINE__);
        }

        // Save partnership info
        $data = [
            "pid" => $partner->getRemotePartnerId(),
            "owner_id" => $partner->getOwnerId(),
            "ts_last_sync" => $partner->getLastSync("Y-m-d H:i:s"),
        ];

        if ($partner->getId()) {
            $this->getDatabase($accountId)->update("entity_sync_partner", $data, ["entity_sync_partner_id" => $partner->getId()]);
        } else {
            $partnerId = $this->getDatabase($accountId)->insert("entity_sync_partner", $data, 'entity_sync_partner_id');
            $partner->setId(intval($partnerId));
        }

        // Save collections
        $this->savePartnerCollections($partner, $accountId);

        return true;
    }

    /**
     * Save partner collections
     *
     * @param Partner $partner The partner that will be used to save its collections
     * @param string $accountId The account that owns the Partner that we are about to save
     */
    private function savePartnerCollections(Partner $partner, string $accountId)
    {
        if (!$partner->getId()) {
            return $this->returnError("Cannot save collections because partner is not saved", __FILE__, __LINE__);
        }

        $collections = $partner->getCollections();
        for ($i = 0; $i < count($collections); $i++) {
            // If this collection was just added to the partner then it may not have the partner id set yet.
            if (!$collections[$i]->getPartnerId()) {
                $collections[$i]->setPartnerId($partner->getId());
            }

            $this->saveCollection($collections[$i], $accountId);
        }

        // Get removed collections
        $removed = $partner->getRemovedCollections();
        foreach ($removed as $removeId) {
            $this->deleteCollection($removeId, $accountId);
        }

        return true;
    }

    /**
     * Get a partner by unique system id
     *
     * @param string $partnerId Netric unique partner id
     * @param string $accountId The account that we will use to get active database handle
     *
     * @return Partner or null if id does not exist
     */
    public function getPartnerById(string $partnerId, string $accountId): ?Partner
    {
        return $this->getPartner($accountId, $partnerId);
    }

    /**
     * Get a partner by the remote partner id
     *
     * @param string $remoteId Remotely provided unique ident
     * @param string $accountId The account that we will use to get active database handle
     *
     * @return Partner or null if id does not exist
     */
    public function getPartnerByRemoteId(string $remoteId, string $accountId): ?Partner
    {
        return $this->getPartner($accountId, null, $remoteId);
    }

    /**
     * Get a partner by either a netric system id or a client partner device id
     *
     * @param string $accountId The account that we will use to get active database handle
     * @param string $systemId System id
     * @param string $remoteId Device id
     *
     * @return Partner or null if id does not exist
     */
    private function getPartner(string $accountId, string $systemId = null, string $remoteId = null): ?Partner
    {
        // Make sure we have at least one id to pull from
        if (null == $systemId && null == $remoteId) {
            return null;
        }

        $sql = "SELECT entity_sync_partner_id, pid, owner_id, ts_last_sync 
                FROM entity_sync_partner WHERE ";

        $params = [];

        // Add condition based on the type of id passed
        if ($systemId) {
            $sql .= "entity_sync_partner_id=:entity_sync_partner_id";
            $params["entity_sync_partner_id"] = $systemId;
        } else {
            $sql .= "pid=:pid";
            $params["pid"] = $remoteId;
        }

        $result = $this->getDatabase($accountId)->query($sql, $params);

        if ($result->rowCount()) {
            $row = $result->fetch();

            $partner = new Partner($this);
            $partner->setId($row['entity_sync_partner_id']);
            $partner->setRemotePartnerId($row['pid']);
            $partner->setOwnerId($row['owner_id']);

            if ($row['ts_last_sync']) {
                $partner->setLastSync(new DateTime($row['ts_last_sync']));
            }

            // Get collections
            $this->populatePartnerCollections($partner, $accountId);

            return $partner;
        }

        // Not found
        return null;
    }

    /**
     * Delete a partner
     *
     * @param Partner $partner The partner to delete
     * @param string $accountId The account that we will use to get active database handle
     *
     * @return bool true on success, false on failure
     */
    public function deletePartner(Partner $partner, string $accountId): bool
    {
        if ($partner->getId()) {
            $params = [];
            $params["entity_sync_partner_id"] = $partner->getId();

            $this->getDatabase($accountId)->delete("entity_sync_partner", $params);
            return true;
        }

        return false;
    }

    /**
     * Populate collections array for a given partner using addCollection
     *
     * @param Partner $partner The partner that we will be loading its collections
     * @param string $accountId The account that we will use to get active database handle
     */
    private function populatePartnerCollections(Partner $partner, string $accountId)
    {
        // Make sure the partner was already loaded
        if (!$partner->getId()) {
            return $this->returnError("Cannot get collections because partner is not saved", __FILE__, __LINE__);
        }

        $sql = "SELECT * FROM entity_sync_collection WHERE partner_id=:partner_id";
        $result = $this->getDatabase($accountId)->query($sql, ["partner_id" => $partner->getId()]);

        foreach ($result->fetchAll() as $row) {
            // Unserialize the conditions
            if ($row['conditions']) {
                $row['conditions'] = unserialize($row['conditions']);
            }

            // Construct a new collection
            if (!$accountId) {
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
            }

            $collection = $this->collectionFactory->create($accountId, $row['type'], $row);
            $collection->fromArray($row);

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
     * @param string $accountId The account that owns the Partner that we are about to save
     * @return bool true on success, false on failure
     */
    private function saveCollection(CollectionInterface $collection, string $accountId)
    {
        if (!$collection->getPartnerId()) {
            return $this->returnError("Cannot save collections because partner is not saved", __FILE__, __LINE__);
        }

        if (!$accountId) {
            return $this->returnError("Account id is a required param", __FILE__, __LINE__);
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

        if ($collection->getCollectionId()) {
            $this->getDatabase($accountId)->update("entity_sync_collection", $data, ["entity_sync_collection_id" => $collection->getCollectionId()]);
        } else {
            $collectionId = $this->getDatabase($accountId)->insert("entity_sync_collection", $data, 'entity_sync_collection_id');
            $collection->setCollectionId(intval($collectionId));
        }

        return true;
    }

    /**
     * Delete a collection
     *
     * @param int $collectionId The id of the collection to delete
     * @param string $accountId The account that owns the Partner that we are about to delete
     * @return bool true on success, false on failure
     */
    private function deleteCollection(int $collectionId, string $accountId)
    {
        if (!$collectionId) {
            return $this->returnError("Collection id is a required param", __FILE__, __LINE__);
        }

        $this->getDatabase($accountId)->delete("entity_sync_collection", ["entity_sync_collection_id" => $collectionId]);
        return true;
    }

    /**
     * Mark a commit as stale for all sync collections
     *
     * @param string $accountId The account that owns the collection
     * @param int $colType Type from EntitySync::COLL_TYPE_*
     * @param int $lastCommitId
     * @param int $newCommitId
     */
    public function setExportedStale(
        string $accountId,
        int $collType,
        string $lastCommitId,
        string $newCommitId
    ) {
        return $this->workerService->doWorkBackground(EntitySyncSetExportedStaleWorker::class, [
            'account_id' => $accountId,
            'collection_type' => $collType,
            'last_commit_id' => $lastCommitId,
            'new_commit_id' => $newCommitId
        ]);
    }

    /**
     * Log that a commit was exported from this collection
     *
     * @param string $accountId The account that owns the collection
     * @param int $colType Type from EntitySync::COLL_TYPE_*
     * @param int $collectionId The unique id of the collection we exported for
     * @param string $uniqueId Unique id of the object sent
     * @param int $commitId The commit id synchronized, if null then delete the entry
     * @return bool true on success, false on failure
     */
    public function logExported(
        string $accountId,
        int $collType,
        int $collectionId,
        string $uniqueId,
        int $commitId = null
    ) {
        return $this->workerService->doWorkBackground(EntitySyncLogExportedWorker::class, [
            'account_id' => $accountId,
            'collection_id' => $collectionId,
            'collection_type' => $collType,
            'unique_id' => $uniqueId,
            'commit_id' => $commitId
        ]);
    }

    /**
     * Log that a commit was exported from this collection
     *
     * @param string $accountId The account that owns the collection
     * @param int $collectionId The id of the collection we are logging changes to
     * @param string $remoteId The foreign unique id of the object being imported
     * @param int $remoteRevision A revision of the remote object (could be an epoch)
     * @param string $localId If imported to a local object then record the id, if null the delete
     * @param int $localRevision The revision of the local object
     */
    public function logImported(
        string $accountId,
        string $collectionId,
        string $remoteId,
        int $remoteRevision = null,
        string $localId = null,
        int $localRevision = null
    ) {
        return $this->workerService->doWorkBackground(EntitySyncLogImportedWorker::class, [
            'account_id' => $accountId,
            'collection_id' => $collectionId,
            'unique_id' => $remoteId,
            'object_id' => $localId,
            'revision' => $localRevision,
            'remote_revision' => $remoteRevision
        ]);
    }
}
