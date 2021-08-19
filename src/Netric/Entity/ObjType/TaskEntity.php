<?php

/**
 * Provides extensions for the Task object
 *
 * @author Marl Tumulak <marl.tumulak@aereus.com>
 * @copyright 2016 Aereus
 */

namespace Netric\Entity\ObjType;

use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Entity\Entity;
use Netric\Entity\EntityInterface;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\EntityDefinition;

/**
 * Task represents a single task entity
 */
class TaskEntity extends Entity implements EntityInterface
{
    /**
     * Constant statuses
     */
    const STATUS_TODO = 'ToDo';
    const STATUS_IN_PROGRESS = 'In-Progress';
    const STATUS_IN_TEST = "In-Test";
    const STATUS_IN_REVIEW = "In-Review";
    const STATUS_COMPLETED = 'Completed';
    const STATUS_DEFERRED = 'Deferred';

    /**
     * Constant Priorities
     */
    const PRIORITY_HIGH = 'High';
    const PRIORITY_MEDIUM = 'Medium';
    const PRIORITY_LOW = 'Low';

    /**
     * Constant Types
     */
    const TYPE_SUPPORT = 'Support';
    const TYPE_ENHANCEMENT = 'Enhancement';
    const TYPE_DEFECT = 'Defect';

    /**
     * Class constructor
     *
     * @param EntityDefinition $def The definition of this type of object
     */
    public function __construct(EntityDefinition $def)
    {
        parent::__construct($def);
    }

    /**
     * Callback function used for derrived subclasses
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @param UserEntity $user The user that is acting on this entity
     */
    public function onBeforeSave(ServiceLocatorInterface $serviceLocator, UserEntity $user)
    {
        if ($this->getValue('status_id')) {
            $this->setValue(
                'done',
                ($this->getValueName('status_id') === self::STATUS_COMPLETED)
            );
        }
    }

    /**
     * Override the default because files can have different icons depending on whether or not this is completed
     *
     * @return string The base name of the icon for this object if it exists
     */
    public function getIconName()
    {
        $done = $this->getValue("done");

        if ($done === 't' || $done === true) {
            return "task_on";
        } else {
            return "task";
        }
    }
}
