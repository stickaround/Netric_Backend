<?php
namespace Netric\Entity;

use Netric\Error\AbstractHasErrors;
use Netric\EntityDefinition\EntityDefinition;
use Netric\EntityDefinition\EntityDefinitionLoader;
use Netric\Log\LogInterface;
use Netric\EntityQuery\Index\IndexInterface;
use Netric\EntityQuery;
use Netric\EntityLoader;
use DateInterval;
use DateTime;
use Netric\FileSystem\FileSystem;

/**
 * Service responsible maintaining entities in the background
 */
class EntityMaintainerService extends AbstractHasErrors
{
    /**
     * Entity index to query entities
     *
     * @var IndexInterface|null
     */
    private $entityIndex = null;

    /**
     * Entity loader to load and save entities

     * @var EntityLoader|null
     */
    private $entityLoader = null;

    /**
     * Application log instance
     *
     * @var LogInterface|null
     */
    private $log = null;

    /**
     * Get entity definition(s)
     *
     * @var EntityDefinitionLoader|null
     */
    private $entityDefinitionLoader = null;

    /**
     * Netric file system service for interacting with files
     *
     * @var FileSystem
     */
    private $fileSystem = null;

    /**
     * EntityMaintainerService constructor
     *
     * @param LogInterface $log
     * @param EntityLoader $entityLoader Loader for entities
     * @param EntityDefinitionLoader $entityDefinitionLoader Loader to get entity definitions
     * @param IndexInterface $entityIndex Index for queries
     * @param FileSystem $fileSystem File system to perform file cleanup tasks
     */
    public function __construct(
        LogInterface $log,
        EntityLoader $entityLoader,
        EntityDefinitionLoader $entityDefinitionLoader,
        IndexInterface $entityIndex,
        FileSystem $fileSystem
    ) {
        $this->log = $log;
        $this->entityLoader = $entityLoader;
        $this->entityDefinitionLoader = $entityDefinitionLoader;
        $this->entityIndex = $entityIndex;
        $this->fileSystem = $fileSystem;
    }

    /**
     * Run all maintenance tasks
     *
     * @return array
     */
    public function runAll()
    {
        $ret = [];
        $ret['trimmed'] = $this->trimAllCappedTypes();
        $ret['purged'] = $this->purgeAllStaleDeleted();
        $ret['deleted_spam'] = $this->deleteOldSpamMessages();
        $ret['deleted_temp_files'] = $this->cleanTempFolder();
        return $ret;
    }

    /**
     * Iterate through all capped entity object types and delete entities past the limit
     *
     * @see trimCappedForType
     * @return array('objType'=>array(deletedIds))|null on failure
     */
    public function trimAllCappedTypes()
    {
        $allDefinitions = $this->entityDefinitionLoader->getAll();

        $ret = [];

        foreach ($allDefinitions as $def) {
            if ($def->capped) {
                $ret[$def->getObjType()] = $this->trimCappedForType($def);
            }
        }

        return $ret;
    }

    /**
     * Trim any entities over the capped limit
     *
     * An entity type can cap the maximum number of entities to store. This function
     * checks each entity object type for a capped field and if set, make sure
     * there are never more than capped number of entities.
     *
     * @param EntityDefinition $def The entity definition to trim
     * @return array|null Array with an id of each entity deleted, or null on failure
     */
    public function trimCappedForType(EntityDefinition $def)
    {
        if (!$def->capped) {
            return null;
        }

        // Buffer storing which entities get deleted to return to the caller
        $deletedEntities = [];

        $query = new EntityQuery($def->getObjType());
        $query->orderBy("ts_updated");
        $query->setLimit(1);
        $this->log->info("EntityMaintainerService->trimCappedForType: getting count for " . $def->getObjType());
        $result = $this->entityIndex->executeQuery($query);
        $totalNum = $result->getTotalNum();

        // If there are more entities than allowed then we should delete them
        if ($totalNum > $def->capped) {
            $numToDelete = $totalNum - $def->capped;
            $this->log->info(
                "EntityMaintainerService->trimCappedForType: trimming " .
                    "$numToDelete from " . $def->getObjType()
            );

            for ($i = 0; $i < $numToDelete; $i++) {
                $entity = $result->getEntity($i);
                $deletedEntities[] = $entity->getId();
                $this->entityLoader->delete($entity);

                // Log it
                $this->log->info(
                    "EntityMaintainerService->trimCappedForType: deleted " . ($i + 1) . " of " . $numToDelete . "  - " . $def->getObjType()
                );
            }
        }

        return $deletedEntities;
    }

    /**
     * Loop through all entity types and purge old deleted entries
     *
     * @param DateTime $cutoff If set then this will be the earliest
     *                         cutoff to start purging, default = today -1 year
     * @return array
     */
    public function purgeAllStaleDeleted(DateTime $cutoff = null)
    {
        $allDefinitions = $this->entityDefinitionLoader->getAll();

        $ret = [];

        foreach ($allDefinitions as $def) {
            $ret[$def->getObjType()] = $this->purgeStaleDeletedForType($def, $cutoff);
        }

        return $ret;
    }

    /**
     * Purge soft deleted entities older than a year
     *
     * @param EntityDefinition $def
     * @param DateTime $cutoff If set then this will be the earliest
     *                         cutoff to start purging, default = today -1 year
     * @return int[] Array of deleted entity IDs
     */
    public function purgeStaleDeletedForType(EntityDefinition $def, DateTime $cutoff = null)
    {
        // Buffer storing which entities get deleted to return to the caller
        $deletedEntities = [];

        if ($cutoff === null) {
            // Get a date that is one year ago today
            $cutoff = new DateTime();
            $cutoff->sub(new DateInterval('P1Y'));
        }

        $query = new EntityQuery($def->getObjType());
        $query->where('f_deleted')->equals(true);
        $query->andWhere("ts_updated")->isLessOrEqualTo($cutoff->getTimestamp());
        $result = $this->entityIndex->executeQuery($query);
        $totalNum = $result->getTotalNum();

        $this->log->info(
            "EntityMaintainerService->purgeStaleDeletedForType: purging $totalNum stale " .
                "entities from " . $def->getObjType()
        );

        // List of IDs to delete
        $toDeleteIds = [];

        // Hard delete all the stale entities
        for ($i = 0; $i < $totalNum; $i++) {
            $entity = $result->getEntity($i);
            $toDeleteIds[] = $entity->getId();
        }

        /*
         * Now delete queued entities. We cannot do it above in the loop
         * because we would be modifying the totalNum as we iterated through
         * it and that is a recipe for desaster.
         */
        foreach ($toDeleteIds as $entityId) {
            $entity = $this->entityLoader->get($def->getObjType(), $entityId);
            if ($entity) {
                $this->entityLoader->delete($entity, true);
                $deletedEntities[] = $entity->getId();
                $this->log->info(
                    "EntityMaintainerService->purgeStaleDeletedForType: deleted " . ($i + 1) . " of " . $totalNum . "  - " . $def->getObjType()
                );
            }
        }

        return $deletedEntities;
    }

    /**
     * Delete old spam messages
     *
     * Later we may want to extend this to all types of entities if they have
     * a flag_spam property set to true. This could be particularly useful for things
     * like lead and case objects that need spam detection.
     *
     * @param DateTime|null $cutoff
     * @return array
     */
    public function deleteOldSpamMessages(DateTime $cutoff = null)
    {
        // Buffer storing which entities get deleted to return to the caller
        $deletedEntities = [];

        if ($cutoff === null) {
            // Get a date that is one year ago today
            $cutoff = new DateTime();
            $cutoff->sub(new DateInterval('P1Y'));
        }

        $query = new EntityQuery('email_message');
        $query->where('flag_spam')->equals(true);
        $query->andWhere("ts_entered")->isLessOrEqualTo($cutoff->getTimestamp());
        $result = $this->entityIndex->executeQuery($query);
        $totalNum = $result->getTotalNum();

        $this->log->info("EntityMaintainerService->deleteSpam: purging $totalNum spam messages");

        // Hard delete all the stale entities
        for ($i = 0; $i < $totalNum; $i++) {
            $entity = $result->getEntity($i);
            $deletedEntities[] = $entity->getId();
            $this->entityLoader->delete($entity);

            // Log it
            $this->log->info(
                "EntityMaintainerService->deleteOldSpamMessages: deleted " . ($i + 1) . " of " . $totalNum . "  spam messages "
            );
        }

        return $deletedEntities;
    }

    /**
     * Delete any files in the temp folder that are older than a cutoff date
     *
     * @param DateTime|null $cutoff
     * @param string $tmpPath Optional override of the system temp path
     * @return array
     */
    public function cleanTempFolder(DateTime $cutoff = null, $tmpPath = FileSystem::PATH_TEMP)
    {
        $deletedFiles = [];
        $tmpFolder = $this->fileSystem->openFolder($tmpPath);

        if (!$tmpFolder) {
            return $deletedFiles;
        }

        if ($cutoff === null) {
            // Get a date that is one year ago today
            $cutoff = new DateTime();
            $cutoff->sub(new DateInterval('P1D'));
        }

        $query = new EntityQuery('file');
        $query->where('folder_id')->equals($tmpFolder->getId());
        $query->andWhere("ts_entered")->isLessOrEqualTo($cutoff->getTimestamp());
        $result = $this->entityIndex->executeQuery($query);
        $totalNum = $result->getTotalNum();

        // Hard delete all files older than the cutoff
        for ($i = 0; $i < $totalNum; $i++) {
            $file = $result->getEntity($i);
            $deletedFiles[] = $file->getId();
            $this->entityLoader->delete($file);

            // Log it
            $this->log->info(
                "EntityMaintainerService->cleanTempFolder: deleted " . ($i + 1) . " of " . $totalNum . "  temp files "
            );
        }

        return $deletedFiles;
    }
}