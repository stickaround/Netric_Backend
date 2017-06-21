<?php
/**
 * @author Sky Stebnicki, sky.stebnicki@aereus.com
 * @copyright Copyright (c) 2017 Aereus Corporation (http://www.aereus.com)
 */
namespace Netric\Entity;

use Netric\Error\AbstractHasErrors;
use Netric\EntityDefinition\EntityDefinition;
use Netric\EntityDefinitionLoader;
use Netric\Log\LogInterface;
use Netric\EntityQuery\Index\IndexInterface;
use Netric\EntityQuery;
use Netric\EntityLoader;

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
     *
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
     * EntityMaintainerService constructor
     *
     * @param LogInterface $log
     * @param EntityLoader $entityLoader
     * @param IndexInterface $entityIndex
     */
    public function __construct(
        LogInterface $log,
        EntityLoader $entityLoader,
        EntityDefinitionLoader $entityDefinitionLoader,
        IndexInterface $entityIndex
    )
    {
        $this->log = $log;
        $this->entityLoader = $entityLoader;
        $this->entityDefinitionLoader = $entityDefinitionLoader;
        $this->entityIndex = $entityIndex;
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
                    "EntityMaintainerService->trimCappedForType: deleted " .
                    ($i + 1) . " of " . $numToDelete . "  - " . $def->getObjType()
                );
            }
        }

        return $deletedEntities;
    }

    /**
     * Purge soft deleted entities older than a year
     *
     * @param EntityDefinition $def
     */
    public function purgeStaleDeletedForType(EntityDefinition $def)
    {
        // TODO: Implement
        throw new \RuntimeException("Not yet implemented");
    }
}