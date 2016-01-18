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

/**
 * Task represents a single task on any entity
 */
class Task extends Entity implements EntityInterface
{
    /**
     * Flag to indicate this was a newly created object
     *
     * @type {bool}
     */
    private $newlyCreated = false;

    /**
     * Callback function used for derrived subclasses
     *
     * @param \Netric\ServiceManager\ServiceLocatorInterface $sm Service manager used to load supporting services
     */
    public function onBeforeSave(ServiceLocatorInterface $sm)
    {
        if (!$this->id)
            $this->newlyCreated = true;
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
     * Return default list of mailboxes which is called by verifyDefaultGroupings in base class.
     *
     * @param string $fieldName the name of the grouping(fkey, fkey_multi) field
     * @return array
     */
    public function getVerifyDefaultGroupingsData($fieldName)
    {
        $checkfor = array();

        if ($fieldName == "category")
            $checkfor = array("Work" => "1", "Personal" => "2", "Other" => "3");

        return $checkfor;
    }

    /**
     * Override the default because files can have different icons depending on whether or not this is completed
     *
     * @return string The base name of the icon for this object if it exists
     */
    public function getIconName()
    {
        $done = $this->getValue("done");

        if ($done == 't' || $done === true)
            return "task_on";
        else
            return "task";
    }
}

