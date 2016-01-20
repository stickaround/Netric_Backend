<?php
/**
 * Provides extensions for the Project object
 *
 * @author Marl Tumulak <marl.tumulak@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\Entity\ObjType;

use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\EntityQuery;
use Netric\EntityQuery\Index\IndexInterface;
use Netric\Entity\Entity;
use Netric\Entity\EntityInterface;

/**
 * Project represents a single project entity
 */
class ProjectEntity extends Entity implements EntityInterface
{
    /**
     * Callback function used for derrived subclasses
     *
     * @param \Netric\ServiceManager\ServiceLocatorInterface $sm Service manager used to load supporting services
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
     * Clone object references from a project id
     *
     * @param ServiceLocatorInterface $sm Service manager used to load supporting services
     * @param Netric\EntityQuery\Index\IndexInterface $index for querying tasks from project
     * @param int $fromPid The project to copy references from
     */
    public function cloneObjectReference(ServiceLocatorInterface $sm, IndexInterface $index, $fromPid)
    {
        $entityLoader = $sm->get("EntityLoader");
        $entityFactory = $sm->get("EntityFactory");
        $currentUser = $sm->getAccount()->getUser();

        $project = $entityLoader->get("project", $fromPid);

        // Get the project details
        $startDate = $project->getValue("ts_created");
        $deadline = $project->getValue("date_deadline");
        $keyFromTs = ($deadline) ? $deadline : $startDate;
        $thisFromTs = ($this->getValue("date_deadline")) ? $this->getValue("date_deadline") : $this->getValue("ts_created");

        // Copy Tasks
        $query = new EntityQuery("task");
        $query->where('project')->equals($fromPid);

        // Execute query and get num results
        $res = $index->executeQuery($query);
        $num = $res->getNum();

        // Loop through each task
        for ($i = 0; $i < $num; $i++) {
            $task = $res->getEntity($i);

            $newTask = $entityFactory->create("task");

            $task->cloneTo($newTask);

            // Move task to this project
            $newTask->setValue("project", $this->getId());

            // Move due date
            if ($task->getValue("deadline"))
            {
                $taskTime = $task->getValue("deadline");
                $diff = $taskTime - $keyFromTs;

                // Calculate new time
                $newTime = $diff + $thisFromTs;
                $newTask->setValue("deadline", date("m/d/Y", $newTime));
            }

            // Save the task
            $entityLoader->save($newTask);
        }
    }
}

