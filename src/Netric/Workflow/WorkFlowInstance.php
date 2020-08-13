<?php

/**
 * @author Sky Stebnicki, sky.stebnicki@aereus.com
 * @copyright Copyright (c) 2015 Aereus Corporation (http://www.aereus.com)
 */

namespace Netric\WorkFlowLegacy;

use Netric\Entity\EntityInterface;

/**
 * Represents a single instance of a workflow running on an entity
 */
class WorkFlowLegacyInstance
{
    /**
     * The unique id of this instance
     *
     * @var int
     */
    private $id = null;

    /**
     * The id of the WorkFlowLegacy being run
     *
     * @var int
     */
    private $workFlowId = null;


    /**
     * The entity this instance is running on
     *
     * @var EntityInterface
     */
    private $entity = null;

    /**
     * When this particular WorkFlowLegacy started
     *
     * @var \DateTime
     */
    private $timeStarted = null;

    /**
     * Flag to indicate whether or not the WorkFlowLegacy instance is completed
     *
     * @var bool
     */
    private $completed = false;

    /**
     * Construct the instance
     *
     * @param int $workFlowId The unique id of the workflow this instance is running
     * @param EntityInterface $entity Optional entity we are running against
     * @param int $instanceId Optional unique id of the instance we are running
     */
    public function __construct($workFlowId, EntityInterface $entity, $instanceId = null)
    {
        if ($instanceId) {
            $this->id = $instanceId;
        }

        if ($workFlowId) {
            $this->workFlowId = $workFlowId;
        }

        $this->entity = $entity;

        // Default to right now
        $this->timeStarted = new \DateTime();
    }

    /**
     * Get the unique id of this instance
     *
     * @return int
     */
    public function getWorkFlowLegacyInstanceId()
    {
        return $this->id;
    }

    /**
     * Set the unique id of this instance
     *
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get the id of the WorkFlowLegacy we are running
     *
     * @return int
     */
    public function getWorkFlowLegacyId()
    {
        return $this->workFlowId;
    }

    /**
     * Set the id of the WorkFlowLegacy we are running
     *
     * @param int $workFlowId
     */
    public function setWorkFlowLegacyId($workFlowId)
    {
        $this->workFlowId = $workFlowId;
    }

    /**
     * Get object type from entity
     *
     * @return string
     */
    public function getObjType()
    {
        return $this->entity->getDefinition()->getObjType();
    }

    /**
     * Get the object type id for the entity we are running against
     *
     * @return int
     */
    public function getObjTypeId()
    {
        return $this->entity->getDefinition()->getEntityDefinitionId();
    }

    /**
     * Get the entity id we are running against in this instance
     *
     * @return int
     */
    public function getEntityId()
    {
        return $this->entity->getEntityId();
    }

    /**
     * Get the time when this instance started
     *
     * @return \DateTime
     */
    public function getTimeStarted()
    {
        return $this->timeStarted;
    }

    /**
     * Set the date and time when this instance started
     *
     * @param \DateTime $timeStarted
     */
    public function setTimeStarted(\DateTime $timeStarted)
    {
        $this->timeStarted = $timeStarted;
    }

    /**
     * Get the entity this instance is running on
     *
     * @return EntityInterface
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set the entity this instance is running on
     *
     * @param EntityInterface $entity
     */
    public function setEntity(EntityInterface $entity)
    {
        $this->entity = $entity;
    }

    /**
     * Check if this instance is complete or not
     *
     * @return bool
     */
    public function isCompleted()
    {
        return $this->completed;
    }

    /**
     * Set whether or not this instance is completed
     *
     * @param $completed
     */
    public function setCompleted($completed)
    {
        $this->completed = $completed;
    }

    /**
     * Make sure the instance is valid for saving
     *
     * @return bool true if ready to save, otherwise false
     */
    public function isValid()
    {
        if (!$this->workFlowId) {
            return false;
        }

        return true;
    }
}
