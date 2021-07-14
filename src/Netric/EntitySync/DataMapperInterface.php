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
     * @param string $accountId The account that we will use to get active database handle
     *
     * @return Partner or null if id does not exist
     */
    public function getPartnerById(string $partnerId, string $accountId): ?Partner;

    /**
     * Get a partner by the remote partner id
     *
     * This is also responsible for loading collections
     *
     * @param string $remoteId Remotely provided unique ident
     * @param string $accountId The account that we will use to get active database handle
     *
     * @return Partner or null if id does not exist
     */
    public function getPartnerByRemoteId(string $remoteId, string $accountId): ?Partner;

    /**
     * Get a partner by id
     *
     * This is also responsible for saving collections for the partner
     *
     * @param Partner $partner Will set the id if new partner
     * @param string $accountId The account that owns the Partner that we are about to save
     * @return bool true on success, false on failure
     */
    public function savePartner(Partner $partner, string $accountId): bool;

    /**
     * Delete a partner
     *
     * @param Partner $partner The partner to delete
     * @param string $accountId The account that we will use to get active database handle
     *
     * @return bool true on success, false on failure
     */
    public function deletePartner(Partner $partner, string $accountId): bool;

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
    );

    /**
     * Log that a commit was exported from this collection
     *
     * @param string $accountId The account that owns the collection
     * @param int $colType Type from \Netric\EntitySync::COLL_TYPE_*
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
    );

    /**
     * Log that a commit was imported to this collection
     *
     * @param string $accountId The account that owns the collection
     * @param int $collectionId The id of the collection we are logging changes to
     * @param string $remoteId The foreign unique id of the object being imported
     * @param int $remoteRevision A revision of the remote object (could be an epoch)
     * @param int $localId If imported to a local object then record the id, if null the delete
     * @param int $localRevision The revision of the local object
     * @return bool true if imported false if failure
     */
    public function logImported(
        string $accountId,
        string $collectionId,
        string $remoteId,
        int $remoteRevision = null,
        string $localId = null,
        int $localRevision = null
    );
}
