<?php

declare(strict_types=1);

namespace Netric\EntitySync\Collection;

use Netric\EntitySync\Commit;
use Netric\Entity\EntityInterface;
use Netric\EntitySync\DataMapperInterface;
use Netric\EntitySync\Commit\CommitManager;
use DateTime;

/**
 * Class used to represent a sync partner or endpoint
 */
abstract class AbstractCollection
{
    /**
     * DataMapper for sync operations
     *
     * @var DataMapperInterface
     */
    protected $dataMapper = null;

    /**
     * Service for managing commits
     *
     * @var CommitManager
     */
    protected $commitManager = null;

    /**
     * Internal id
     *
     * @var int
     */
    protected $collectionId = 0;

    /**
     * Partner id
     *
     * @var string
     */
    protected $partnerId = '';

    /**
     * Object type name
     *
     * @var string
     */
    protected $objType = '';

    /**
     * Object type name
     *
     * @var string
     */
    protected $fieldName = '';

    /**
     * Last sync time
     *
     * @var DateTime
     */
    protected $lastSync = null;

    /**
     * Last commit id that was exported from this colleciton
     *
     * @var int
     */
    protected $lastCommitId = 0;

    /**
     * Conditions array
     *
     * @var array
     *  (array("blogic", "field", "operator", "condValue"));
     */
    protected $conditions = [];

    /**
     * Cache change results in a revision increment
     *
     * @var int
     */
    protected $revision = 1;

    /**
     * Last time this collection was checked for updates for mutiple subsequent calls
     *
     * @var float
     */
    protected $lastRevisionCheck = null;

    /**
     * Constructor
     *
     * @param DataMapperInterface $dm The sync datamapper
     */
    public function __construct(
        DataMapperInterface $dataMapper,
        Commit\CommitManager $commitManager
    ) {
        $this->dataMapper = $dataMapper;
        $this->commitManager = $commitManager;
    }

    /**
     * Get the head commit for a given collection type
     *
     * @return string The last commit id for the type of data we are watching
     */
    abstract protected function getCollectionTypeHeadCommit();

    /**
     * Set the last commit id synchronized
     *
     * @param int $commitId
     */
    public function setLastCommitId(int $commitId)
    {
        $this->lastCommitId = $commitId;
    }

    /**
     * Get the last commit ID that was syncrhonzied/exported from this collection
     *
     * @return int|null
     */
    public function getLastCommitId(): int
    {
        return $this->lastCommitId;
    }

    /**
     * Set the id of this collection
     *
     * @param int $collectionId
     */
    public function setCollectionId(int $collectionId)
    {
        $this->collectionId = $collectionId;
    }

    /**
     * Get the unique id of this collection
     *
     * @return int
     */
    public function getCollectionId(): int
    {
        return $this->collectionId;
    }

    /**
     * Set the partner id of this collection
     *
     * @param string $pid
     */
    public function setPartnerId(string $pid)
    {
        $this->partnerId = $pid;
    }

    /**
     * Get the partner id of this collection
     *
     * @return string
     */
    public function getPartnerId()
    {
        return $this->partnerId;
    }

    /**
     * Set the object type if applicable
     *
     * @param string $objType
     */
    public function setObjType(string $objType)
    {
        $this->objType = $objType;
    }

    /**
     * Get the object type if applicable
     *
     * @return string
     */
    public function getObjType(): string
    {
        return $this->objType;
    }

    /**
     * Set the name of a grouping field if set
     *
     * @param string $fieldName Name of field to set
     */
    public function setFieldName(string $fieldName)
    {
        $this->fieldName = $fieldName;
    }

    /**
     * Get the name of a grouping field if set
     *
     * @return string
     */
    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    /**
     * Set last sync timestamp
     *
     * @param DateTime $timestamp When the partnership was last synchronized
     */
    public function setLastSync(DateTime $timestamp)
    {
        $this->lastSync = $timestamp;
    }

    /**
     * Set the revision
     *
     * @param int $revision
     */
    public function setRevision(int $revision)
    {
        $this->revision = $revision;
    }

    /**
     * Get the revision
     *
     * @return int
     */
    public function getRevision(): int
    {
        return $this->revision;
    }

    /**
     * Set conditions with array
     *
     * @param array $conditions array(array("blogic", "field", "operator", "condValue"))
     */
    public function setConditions(array $conditions)
    {
        $this->conditions = $conditions;
    }

    /**
     * Get conditions
     *
     * @return array(array("blogic", "field", "operator", "condValue"))
     */
    public function getConditions(): array
    {
        return $this->conditions;
    }

    /**
     * Get last sync timestamp
     *
     * @param string $strFormat If set format the DateTime object as a string and return
     * @return DateTime|string $timestamp When the partnership was last synchronized
     */
    public function getLastSync($strFormat = null)
    {
        // If desired return a formatted string version of the timestamp
        if ($strFormat && $this->lastSync) {
            return $this->lastSync->format($strFormat);
        }

        return $this->lastSync;
    }

    /**
     * Determine if this collection is behind the head commit of data it is watching
     *
     * @return bool true if behind, false if no changes have been made since last sync
     */
    public function isBehindHead(): bool
    {
        // Get last commit id for this collection
        $headCommit = $this->getCollectionTypeHeadCommit();

        // Get the current commit for this collection
        $lastCollectionCommit = $this->getLastCommitId();

        return ($lastCollectionCommit < $headCommit);
    }

    /**
     * Log that a commit was exported from this collection
     *
     * @param string $uniqueId The unique id of the object we sent
     * @param int $commitId The unique id of the commit we sent
     * @return bool
     */
    public function logExported(string $uniqueId, int $commitId = null)
    {
        if (!$this->getCollectionId()) {
            return false;
        }

        $ret = $this->dataMapper->logExported($this->getType(), $this->getCollectionId(), $uniqueId, $commitId);

        // Check if there was a problem because that should never happen
        if (!$ret) {
            throw new \Exception("Could not log exported sync entry: " . $this->dataMapper->getLastError());
        }

        return $ret;
    }

    /**
     * Get a list of previously exported commits that have been updated
     *
     * This is used to get a list of objects that were previously synchornized
     * but were later either moved outside the collection (no longer met conditions)
     * or deleted.
     *
     * NOTE: THIS MUST BE RUN AFTER GETTING NEW/CHANGED OBJECTS IN A COLLECTION.
     *  1. Get all new commits from last_commit and log the export
     *  2. Once all new commit updates were retrieved for a collection then call this
     *  3. Once this returns empty then fast-forward this collection to head
     *
     * @return array(array('id'=>objectId, 'action'=>'delete'))
     */
    public function getExportedStale()
    {
        if (!$this->getCollectionId()) {
            return [];
        }

        $staleStats = [];

        $stale = $this->dataMapper->getExportedStale($this->getCollectionId());
        foreach ($stale as $oid) {
            $staleStats[] = array(
                "id" => $oid,
                "action" => 'delete',
            );
        }

        return $staleStats;
    }

    /**
     * Get a stats of the difference between an import and what is stored locally
     *
     * @param array $importList Array of arrays with the following param for each object {uid, revision}
     * @return array(
     *      array(
     *          'uid', // Unique id of foreign object
     *          'local_id', // Local entity/object (same thing) id
     *          'action', // 'chage'|'delete'
     *          'revision' // Revision of local entity at time of last import
     *      );
     */
    public function getImportChanged(array $importList)
    {
        if (!$this->getCollectionId()) {
            return [];
        }

        // Get previously imported list and set the default action to delete
        // --------------------------------------------------------------------
        $changes = $this->dataMapper->getImported($this->getCollectionId());
        $numChanges = count($changes);
        for ($i = 0; $i < $numChanges; $i++) {
            $changes[$i]['action'] = 'delete';
        }

        // Loop through both lists and look for differences
        // --------------------------------------------------------------------
        foreach ($importList as $item) {
            $found = false;

            // Check existing
            for ($i = 0; $i < $numChanges; $i++) {
                if ($changes[$i]['remote_id'] == $item['remote_id']) {
                    if ($changes[$i]['remote_revision'] == $item['remote_revision']) {
                        array_splice($changes, $i, 1); // no changes, remove
                        $numChanges = count($changes);
                    } else {
                        $changes[$i]['action'] = 'change'; // was updated on remote source
                        $changes[$i]['remote_revision'] = $item['remote_revision'];
                    }

                    $found = true;
                    break;
                }
            }

            if (!$found) { // not found locally or revisions do not match
                $changes[] = array(
                    "remote_id" => $item['remote_id'],
                    "remote_revision" => $item['remote_revision'],
                    "local_id" => null,
                    "local_revision" => isset($item['local_revision']) ? $item['local_revision'] : 1,
                    "action" => "change",
                );

                // Update count so we can stay in bounds in the above loop
                $numChanges = count($changes);
            }
        }

        return $changes;
    }

    /**
     * Log an imported object
     *
     * @param string $remoteId The foreign unique id of the object being imported
     * @param int $remoteRevision A revision of the remote object (could be an epoch)
     * @param string $localId If imported to a local object then record the id, if null the delete
     * @param int $localRevision The revision of the local object
     * @return bool true if imported false if failure
     * @throws \InvalidArgumentException
     */
    public function logImported(
        string $remoteId,
        int $remoteRevision = null,
        string $localId = null,
        int $localRevision = null
    ) {
        if (!$this->getCollectionId()) {
            return false;
        }

        if (!$remoteId) {
            throw new \InvalidArgumentException("remoteId was not set and is required.");
        }

        /*
         * When we import, we should also log that it was exported since
         * we know that the remote client has the object already.
         */
        if ($localId && $localRevision) {
            $this->logExported($localId, $localRevision);
        }

        // Log the import and return the results
        return $this->dataMapper->logImported(
            $this->getCollectionId(),
            $remoteId,
            $remoteRevision,
            $localId,
            $localRevision
        );
    }
}
