<?php

declare(strict_types=1);

namespace Netric\EntitySync;

interface DataMapperInterface
{
    /**
     * Get a partner by unique system id
     *
     * This is also responsible for loading collections
     *
     * @param string $partnerId Netric unique partner id
     * @return Partner or null if id does not exist
     */
    public function getPartnerById(string $partnerId): ?Partner;

    /**
     * Get a partner by the remote partner id
     *
     * This is also responsible for loading collections
     *
     * @param string $partnerId Remotely provided unique ident
     * @return Partner or null if id does not exist
     */
    public function getPartnerByPartnerId(string $partnerId): ?Partner;

    /**
     * Get a partner by id
     *
     * This is also responsible for saving collections for the partner
     *
     * @param Partner $partner Will set the id if new partner
     * @return bool true on success, false on failure
     */
    public function savePartner(Partner $partner): bool;

    /**
     * Delete a partner
     *
     * @param Partner $partner The partner to delete
     * @return bool true on success, false on failure
     */
    public function deletePartner(Partner $partner): bool;

    /**
     * Mark a commit as stale for all sync collections
     *
     * @param int $colType Type from EntitySync::COLL_TYPE_*
     * @param int $lastCommitId
     * @param int $newCommitId
     */
    public function setExportedStale(
        int $collType,
        int $lastCommitId,
        int $newCommitId
    );

    /**
     * Log that a commit was exported from this collection
     *
     * @param int $colType Type from \Netric\EntitySync::COLL_TYPE_*
     * @param int $collectionId The unique id of the collection we exported for
     * @param string $uniqueId Unique id of the object sent
     * @param int $commitId The commit id synchronized, if null then delete the entry
     * @return bool true on success, false on failure
     */
    public function logExported(
        int $collType,
        int $collectionId,
        string $uniqueId,
        int $commitId = null
    );

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
        string $localId = null,
        int $localRevision = null
    );

    /**
     * Get a list of previously exported commits that have been updated
     *
     * This is used to get a list of objects that were previously synchornized
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
     * @return array(array('id'=>objectId, 'action'=>'delete'))
     */
    public function getExportedStale(int $collectionId);

    /**
     * Get a list of previously imported objects
     *
     * @param int $collectionId The id of the collection we get stats for
     * @return array(array('uid', 'local_id', 'revision'))
     */
    public function getImported(int $collectionId);
}
