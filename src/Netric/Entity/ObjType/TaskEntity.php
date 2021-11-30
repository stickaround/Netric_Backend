<?php

namespace Netric\Entity\ObjType;

use Netric\Entity\Entity;
use Netric\Entity\EntityInterface;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\ObjectTypes;
use Netric\EntityGroupings\GroupingLoader;
use Netric\EntityDefinition\EntityDefinition;
use Netric\ServiceManager\ServiceLocatorInterface;
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
     * Grouping loader used to get user groups
     *
     * @var GroupingLoader
     */
    private $groupingLoader = null;

    /**
     * Class constructor
     *
     * @param EntityDefinition $def The definition of this type of object
     * @param GroupingLoader $groupingLoader Handles the loading and saving of groupings
     */
    public function __construct(
        EntityDefinition $def,
        GroupingLoader $groupingLoader
    ) {
        $this->groupingLoader = $groupingLoader;
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
        // If the password was updated for this user then encrypt it
        if ($this->fieldValueChanged("is_closed") && $this->getValue("is_closed") && $this->getValue("status_id") === '') {
            $statusGroups = $this->groupingLoader->get(ObjectTypes::TASK . '/status_id', $this->getAccountId());
            // Check for status completed groud id if empty
            if ($statusGroups->getByName(self::STATUS_COMPLETED)->groupId) {
                $completedId = $statusGroups->getByName(self::STATUS_COMPLETED)->groupId;
                $this->setValue("status_id", $completedId);
            }
        }
    }

    /**
     * Override the default because files can have different icons depending on whether or not this is completed
     *
     * @return string The base name of the icon for this object if it exists
     */
    public function getIconName()
    {
        $closed = $this->getValue('is_closed');

        if ($closed === true) {
            return "task_on";
        } else {
            return "task";
        }
    }
}
