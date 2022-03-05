<?php

/**
 * Provide user extensions to base Entity class
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */

namespace Netric\Entity\ObjType;

use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\EntityDefinition\EntityDefinition;
use Netric\Entity\EntityLoader;
use Netric\Entity\Entity;
use Netric\Entity\EntityInterface;
use Netric\EntityQuery\Index\IndexInterface;
use Netric\EntityQuery\EntityQuery;
use Netric\EntityDefinition\ObjectTypes;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityGroupings\GroupingLoader;

/**
 * Folder for entity
 */
class FolderEntity extends Entity implements EntityInterface
{
    /**
     * Index to query entities
     *
     * @var IndexInterface
     */
    private $entityIndex = null;

    /**
     * @param EntityDefinition $def The definition of this type of object
     * @param EntityLoader $entityLoader The loader for a specific entity
     * @param IndexInterface $entityQueryIndex Index to find entities
     */
    public function __construct(
        EntityDefinition $def,
        EntityLoader $entityLoader,
        GroupingLoader $groupingLoader,
        IndexInterface $entityQueryIndex
    ) {
        $this->entityIndex = $entityQueryIndex;

        parent::__construct($def, $entityLoader, $groupingLoader);
    }

    /**
     * Callback function used for derrived subclasses
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @param UserEntity $user The user that is acting on this entity
     */
    public function onBeforeSave(ServiceLocatorInterface $serviceLocator, UserEntity $user)
    {
        $path = $this->getValue("name");

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
     * Checks before a hard delete
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param UserEntity $user The user that is acting on this entity
     */
    public function onBeforeDeleteHard(ServiceLocatorInterface $serviceLocator, UserEntity $user)
    {
        if ($this->getValue("f_system") === true) {
            throw new \RuntimeException("A system folder cannot be deleted: " . $this->getFullPath());
        }
    }
}
