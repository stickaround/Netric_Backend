<?php

/**
 * Provide user extensions to base Entity class
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */

namespace Netric\Entity\ObjType;

use Netric\ServiceManager;
use Netric\EntityDefinition\EntityDefinition;
use Netric\Entity\EntityLoader;
use Netric\Entity\Entity;
use Netric\Entity\EntityInterface;
use Netric\EntityQuery\Index\IndexInterface;
use Netric\EntityQuery;
use Netric\EntityDefinition\ObjectTypes;

/**
 * Folder for entity
 */
class FolderEntity extends Entity implements EntityInterface
{
    /**
     * Entity loader for getting files and folders by id
     *
     * @var EntityLoader
     */
    private $entityLoader = null;

    /**
     * Index to query entities
     *
     * @var IndexInterface
     */
    private $entityIndex = null;

    /**
     * @param EntityDefinition $def
     * @param EntityLoader $entityLoader
     * @param IndexInterface $entityQueryIndex Index to find entities
     */
    public function __construct(EntityDefinition $def, EntityLoader $entityLoader, IndexInterface $entityQueryIndex)
    {
        $this->entityLoader = $entityLoader;
        $this->entityIndex = $entityQueryIndex;
        parent::__construct($def, $entityLoader);
    }

    /**
     * Callback function used for derrived subclasses
     *
     * @param ServiceManager\AccountServiceManagerInterface $sm Service manager used to load supporting services
     */
    public function onBeforeSave(ServiceManager\AccountServiceManagerInterface $sm)
    {
        $path = $this->getValue("name");

        // If this folder has no parent_id and we are not dealing with root folder
        if (!$this->getValue("parent_id") && $path !== '/') {
            $rootFolderEntity = $this->getRootFolder();

            // This will avoid any circular reference to the root folder entity
            if ($rootFolderEntity && $rootFolderEntity->getEntityId() != $this->getEntityId()) {
                $this->setValue("parent_id", $rootFolderEntity->getEntityId());
            }
        }

        // Make sure that parent_id and entity id is not the same
        if ($this->getEntityId() && $this->getEntityId() == $this->getValue("parent_id")) {
            throw new \RuntimeException("Invalid parent id. Cannot set its own id as parent id.");
        }

        // Check to see if they are trying to delete a system directory - should never happen
        if ($this->getValue("f_system") === true && $this->getValue("f_deleted") === true) {
            throw new \RuntimeException("A system folder cannot be deleted: " . $this->getFullPath());
        }
    }

    /**
     * Callback function used for derrived subclasses
     *
     * @param ServiceManager\AccountServiceManagerInterface $sm Service manager used to load supporting services
     */
    public function onAfterSave(ServiceManager\AccountServiceManagerInterface $sm)
    {
    }

    /**
     * Checks before a hard delete
     *
     * @param ServiceManager\AccountServiceManagerInterface $sm
     */
    public function onBeforeDeleteHard(ServiceManager\AccountServiceManagerInterface $sm)
    {
        if ($this->getValue("f_system") === true) {
            throw new \RuntimeException("A system folder cannot be deleted: " . $this->getFullPath());
        }
    }

    /**
     * Get the full path for this folder relative to the root
     */
    public function getFullPath()
    {
        $path = $this->getValue("name");

        // If we have no parent then we are the root (or should be)
        if (!$this->getValue("parent_id") && $path === '/') {
            return $path;
        } elseif (!$this->getValue("parent_id")) {
            // This condition should never happen, but just in case
            // TODO: throw exception?

            // Right now, just return the root path, so it wont create a blank folder
            return "/";
        }

        $parentFolder = $this->entityLoader->getByGuid($this->getValue("parent_id"));
        $pre = $parentFolder->getFullPath();

        // If our parent is the root, then just absolute path to root and avoid returing '//"
        if ($pre === '/') {
            return "/" . $path;
        } else {
            return $pre . "/" . $path;
        }
    }

    /**
     * Move a folder to a new parent folder
     *
     * @param Folder $newParentFolder The folder to move this folder to
     * @return bool true on sucess, false on failure
     */
    public function move(Folder $newParentFolder)
    {
        if (!$newParentFolder->getEntityId()) {
            // TODO: Maybe throw exception since this should probably never happen?
            return false;
        }

        $this->setValue("parent_id", $newParentFolder->getEntityId());
        return true;
    }

    /**
     * Function that will get the root folder
     *
     * @return EntityInterface|null Returns the root folder entity if exists, otherwise null
     */
    public function getRootFolder()
    {
        $query = new EntityQuery(ObjectTypes::FOLDER);
        $query->where("parent_id")->equals("");
        $query->andWhere("name")->equals("/");
        $query->andWhere("f_system")->equals(true);

        $result = $this->entityIndex->executeQuery($query);
        if ($result->getNum()) {
            return $result->getEntity();
        }

        return null;
    }
}
