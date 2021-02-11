<?php

declare(strict_types=1);

namespace Netric\EntitySync\Collection;

use DateTime;

/**
 * Sync collection interface
 */
interface CollectionInterface
{
    /**
     * Get a id if it is saved
     *
     * @return int
     */
    public function getCollectionId(): int;

    /**
     * Set the id of this collection
     *
     * @param int $collectionId
     */
    public function setCollectionId(int $collectionId);

    /**
     * Get a stats list of what has changed locally since the last sync
     *
     * @param bool $autoFastForward If true (default) then fast-forward collection commit_id on return
     * @param DateTime $limitUpdatesAfter If set, only pull updates after a specific date
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
    );

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
    public function getImportChanged(array $importList);

    /**
     * Get a collection type id
     *
     * @return int Type from \Netric\EntitySync::COLL_TYPE_*
     */
    public function getType();

    /**
     * Fast forward this collection to current head which resets it to only get future changes
     */
    public function fastForwardToHead();

    /**
     * Set the object type if applicable
     *
     * @param string $objType
     */
    public function setObjType(string $objType);

    /**
     * Get the object type if applicable
     *
     * @return string
     */
    public function getObjType(): string;

    /**
     * Set conditions with array
     *
     * @param array $conditions array(array("blogic", "field", "operator", "condValue"))
     */
    public function setConditions(array $conditions);

    /**
     * Get conditions
     *
     * @return array(array("blogic", "field", "operator", "condValue"))
     */
    public function getConditions(): array;

    /**
     * Set the last commit id synchronized
     *
     * @param int $commitId
     */
    public function setLastCommitId(int $commitId);

    /**
     * Log that a commit was exported from this collection
     *
     * @param string $uniqueId The unique id of the object we sent
     * @param int $commitId The unique id of the commit we sent
     * @return bool
     */
    public function logExported(string $uniqueId, int $commitId);

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
    );

    /**
     * Load collection data from an associative array
     *
     * @param array $data
     */
    public function fromArray($data);

    /**
     * Set the account that owns this collection
     *
     * @param string $accountId The account that owns this collection
     */
    public function setAccountId(string $accountId);

    /**
     * Get the account that owns this collection
     *
     * @return string Returns the account that owns this collection
     */
    public function getAccountId();
}
