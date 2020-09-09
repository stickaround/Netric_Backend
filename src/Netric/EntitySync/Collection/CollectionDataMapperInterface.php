<?php

declare(strict_types=1);

namespace Netric\EntitySync\Collection;

interface CollectionDataMapperInterface
{
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
     * @param string $accountId The account that owns the collection
     * @return array(array('id'=>objectId, 'action'=>'delete'))
     */
    public function getExportedStale(int $collectionId, string $accountId);

    /**
     * Get a list of previously imported objects
     *
     * @param int $collectionId The id of the collection we get stats for
     * @param string $accountId The account that owns the collection
     * @return array(array('uid', 'local_id', 'revision'))
     */
    public function getImported(int $collectionId, string $accountId);
}
