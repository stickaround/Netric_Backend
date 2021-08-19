<?php

/**
 * Provides extensions for the Project object
 *
 * @author Marl Tumulak <marl.tumulak@aereus.com>
 * @copyright 2016 Aereus
 */

namespace Netric\Entity\ObjType;

use Netric\Entity\Entity;
use Netric\Entity\EntityInterface;
use Netric\Entity\EntityLoader;
use Netric\EntityQuery\EntityQuery;
use Netric\EntityQuery\Index\IndexInterface;
use Netric\EntityDefinition\ObjectTypes;
use Netric\EntityDefinition\EntityDefinition;

/**
 * Project represents a single project entity
 */
class ProjectEntity extends Entity implements EntityInterface
{
    /**
     * Entity index for running queries against
     *
     * @var IndexInterface
     */
    private $indexInterface = null;

    /**
     * The loader for a specific entity
     *
     * @var EntityLoader
     */
    private $entityLoader = null;

    /**
     * Class constructor
     *
     * @param EntityDefinition $def The definition of this type of object
     * @param EntityLoader $entityLoader The loader for a specific entity
     * @param IndexInterface $index IndexInterface for running queries against
     */
    public function __construct(EntityDefinition $def, EntityLoader $entityLoader, IndexInterface $indexInterface)
    {
        $this->entityLoader = $entityLoader;
        $this->indexInterface = $indexInterface;

        parent::__construct($def);
    }

    /**
     * Perform a clone of the project entity to another project
     *
     * @param Entity $toEntity The entity that we are cloning to
     */
    public function cloneTo(Entity $toEntity)
    {
        // Get the id of $toEntity since ::cloneTo() will set the $toEntity's id to null. We assign it back later after cloning this project
        $toEntityId = $toEntity->getEntityId();

        // Perform the shallow copy of fields
        parent::cloneTo($toEntity);

        // Assign back the $toEntity Id since it was set to null when cloning this project using ::cloneTo().
        $toEntity->setValue('entity_id', $toEntityId);

        // Query the tasks of this project entity
        $query = new EntityQuery(ObjectTypes::TASK, $this->getAccountId());
        $query->where('project')->equals($this->getEntityId());

        // Execute query and get num results
        $res = $this->indexInterface->executeQuery($query);
        $num = $res->getNum();

        // Get the owner of the project
        $userProjectOwner = $this->entityLoader->getEntityById($this->getValue('owner_id'), $this->getAccountId());

        // Loop through each task of this project entity
        for ($i = 0; $i < $num; $i++) {
            $task = $res->getEntity($i);

            // Create a new task to be cloned
            $toTask = $this->entityLoader->create(ObjectTypes::TASK, $this->getAccountId());

            $task->cloneTo($toTask);

            // Move task to the project entity we are cloning
            $toTask->setValue("project", $toEntity->getEntityId());

            // Save the task
            $this->entityLoader->save($toTask, $userProjectOwner);
        }
    }
}
