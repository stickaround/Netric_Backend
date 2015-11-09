<?php
/**
 * Provides extensions for the File object
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\Entity\ObjType;

use Netric\Entity;

use Netric\ServiceManager\ServiceLocatorInterface;

/**
 * Folder for entity
 */
class File extends Entity implements \Netric\Entity\EntityInterface
{
    /**
     * File handle reference
     *
     * @var resource
     */
    private $fileHandle = null;

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
     * Called right before the endity is purged (hard delete)
     *
     * @param ServiceLocatorInterface $sm Service manager used to load supporting services
     */
    public function onBeforeDeleteHard(ServiceLocatorInterface $sm)
    {
        $fileStore = $sm->get("Netric/FileSystem/FileStore/FileStore");

        // When this file gets purged we should delete the raw data from the fileStore
        $fileStore->deleteFile($this);
    }

    /**
     * Get a file handle if set
     *
     * @return resource
     */
    public function getFileHandle()
    {
        return $this->fileHandle;
    }

    /**
     * Set a file handle
     *
     * @var resource $fileHandle
     */
    public function setFileHandle($fileHandle)
    {
        $this->fileHandle = $fileHandle;
    }
}
