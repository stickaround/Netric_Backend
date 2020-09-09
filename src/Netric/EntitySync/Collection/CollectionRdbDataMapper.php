<?php

declare(strict_types=1);

namespace Netric\EntitySync\Collection;

use Netric\Db\Relational\RelationalDbContainerInterface;
use Netric\Db\Relational\RelationalDbContainer;
use Netric\Db\Relational\RelationalDbInterface;
use DateTime;

/**
 * Relational database datamapper for Entity Sync Collection
 */
class CollectionRdbDataMapper extends AbstractDataMapper implements CollectionDataMapperInterface
{
    /**
     * Database container
     *
     * @var RelationalDbContainer
     */
    private $databaseContainer = null;

    /**
     * Construct and initialize dependencies
     *
     * @param RelationalDbContainer $dbContainer Handles the database actions
     */
    public function __construct(RelationalDbContainer $dbContainer)
    {        
        $this->databaseContainer = $dbContainer;
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
     * @param string $accountId The account that owns the collection
     * @return int[] Array of stale IDs
     */
    public function getExportedStale(int $collectionId, string $accountId)
    {
        if (!is_numeric($collectionId)) {
            throw new \Exception("A valid $collectionId is a required param.");
        }

        $staleStats = [];

        // Get everything from the exported log that is set as stale
        $sql = "SELECT unique_id
                FROM entity_sync_export 
    			      WHERE collection_id=:collection_id AND new_commit_id IS NOT NULL
                LIMIT 1000;";

        $result = $this->getDatabase($accountId)->query($sql, ["collection_id" => $collectionId]);

        foreach ($result->fetchAll() as $row) {
            $staleStats[] = $row["unique_id"];
        }

        return $staleStats;
    }

    /**
     * Get a list of previously imported objects
     *
     * @param int $collectionId The id of the collection we get stats for
     * @param string $accountId The account that owns the collection
     * @throws \InvalidArgumentException If there is no collection id
     * @throws \Exception if we cannot query the database
     * @return array(array('remote_id', 'remote_revision', 'local_id', 'local_revision'))
     */
    public function getImported(int $collectionId, string $accountId)
    {
        if (!is_numeric($collectionId)) {
            throw new \InvalidArgumentException("A valid $collectionId is a required param.");
        }

        $importedStats = [];

        // Get everything from the exported log that is set as stale
        $sql = "SELECT unique_id, remote_revision, object_id, revision 
                FROM entity_sync_import
    			      WHERE collection_id=:collection_id";

        $result = $this->getDatabase($accountId)->query($sql, ["collection_id" => $collectionId ]);
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
}