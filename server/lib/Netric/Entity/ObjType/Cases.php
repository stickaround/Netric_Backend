<?php
/**
 * Provides extensions for the Cases object
 *
 * @author Marl Tumulak <marl.tumulak@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\Entity\ObjType;

use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Entity\Entity;
use Netric\Entity\EntityInterface;

/**
 * Cases represents a single cases on any entity
 */
class Cases extends Entity implements EntityInterface
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
}

