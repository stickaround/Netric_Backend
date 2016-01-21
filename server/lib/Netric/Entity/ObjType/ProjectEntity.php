<?php
/**
 * Provides extensions for the Project object
 *
 * @author Marl Tumulak <marl.tumulak@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\Entity\ObjType;

use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Entity\Entity;
use Netric\Entity\EntityInterface;
use Netric\EntityLoader;
use Netric\EntityQuery;
use Netric\EntityQuery\Index\IndexInterface;

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
    public function __construct(&$def, EntityLoader $entityLoader, IndexInterface $indexInterface)
    {
        parent::__construct($def);

        $this->entityLoader = $entityLoader;
        $this->indexInterface = $indexInterface;
    }

    /**
     * Callback function used for derrived subclasses
     *
     * @param ServiceLocatorInterface $sm Service manager used to load supporting services
     */
    public function onBeforeSave(ServiceLocatorInterface $sm)
    {
    }

    /**
     * Callback function used for derrived subclasses
     *
     * @param ServiceLocatorInterface $sm Service manager used to load supporting services
     */
    public function onAfterSave(ServiceLocatorInterface $sm)
    {
    }

    /**
     * Called right before the entity is purged (hard delete)
     *
     * @param ServiceLocatorInterface $sm Service manager used to load supporting services
     */
    public function onBeforeDeleteHard(ServiceLocatorInterface $sm)
    {
    }

    /**
     * Perform a clone of the project entity to another project
     *
     * @param Entity $toEntity The entity that we are cloning to
     */
    public function cloneTo(Entity $toEntity)
    {
        // Get data from the project entity we are cloning
        $toEntityId = $toEntity->getId();
        $startDate = $toEntity->getValue("ts_created");
        $deadline = $toEntity->getValue("date_deadline");
        $toFromTs = ($deadline) ? $deadline : $startDate;
        $thisFromTs = ($this->getValue("date_deadline")) ? $this->getValue("date_deadline") : $this->getValue("ts_created");

        // Perform the shallow copy of fields
        parent::cloneTo($toEntity);

        // Override $toEntity Id and Dates
        $toEntity->setId($toEntityId);
        $toEntity->setValue("ts_created", $startDate);
        $toEntity->setValue("date_deadline", $deadline);

        // Query the tasks of this project entity
        $query = new EntityQuery("task");
        $query->where('project')->equals($this->getId());

        // Execute query and get num results
        $res = $this->indexInterface->executeQuery($query);
        $num = $res->getNum();

        // Loop through each task of this project entity
        for ($i = 0; $i < $num; $i++) {
            $task = $res->getEntity($i);

            // Create a new task to be cloned
            $toTask = $this->entityLoader->create("task");

            $task->cloneTo($toTask);

            // Move task to the project entity we are cloning
            $toTask->setValue("project", $toEntityId);

            // Move due date
            if ($task->getValue("deadline"))
            {
                $taskTime = $task->getValue("deadline");
                $diff = $taskTime - $thisFromTs;

                // Calculate new time
                $newTime = $diff + $toFromTs;
                $toTask->setValue("deadline", date("m/d/Y", $newTime));
            }

            // Save the task
            $this->entityLoader->save($toTask);
        }
    }
}

