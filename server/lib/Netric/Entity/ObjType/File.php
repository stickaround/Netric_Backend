<?php
/**
 * Provides extensions for the File object
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\Entity\ObjType;

/**
 * Folder for entity
 */
class File extends \Netric\Entity implements \Netric\Entity\EntityInterface
{
    /**
     * Callback function used for derrived subclasses
     *
     * @param \Netric\ServiceManager\ServiceLocatorInterface $sm Service manager used to load supporting services
     */
    public function onBeforeSave(\Netric\ServiceManager\ServiceLocatorInterface $sm)
    {
    }

    /**
     * Callback function used for derrived subclasses
     *
     * @param \Netric\ServiceManager\ServiceLocatorInterface $sm Service manager used to load supporting services
     */
    public function onAfterSave(\Netric\ServiceManager\ServiceLocatorInterface $sm)
    {
    }
}
