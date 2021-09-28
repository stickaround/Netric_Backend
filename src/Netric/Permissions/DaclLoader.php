<?php

namespace Netric\Permissions;

use Netric\Entity\EntityInterface;
use Netric\EntityDefinition\EntityDefinition;
use Netric\Entity\EntityLoader;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\ObjectTypes;
use Netric\EntityGroupings\GroupingLoader;

/**
 * Identity mapper for DACLs to make sure we are only loading each one once
 */
class DaclLoader
{
    /**
     * Entity loader to get parent entities
     *
     * @var EntityLoader
     */
    private $entityLoader = null;

    /**
     * Entity grouping loader for getting user groups
     *
     * @var GroupingLoader
     */
    private $groupingLoader = null;

    /**
     * Class constructor
     *
     * @param EntityLoader $entityLoader The loader for the entity
     */
    public function __construct(EntityLoader $entityLoader, GroupingLoader $groupingLoader)
    {
        $this->entityLoader = $entityLoader;
        $this->groupingLoader = $groupingLoader;
    }

    /**
     * Get a DACL for an entity
     *
     * 1. Check if the entity has its own dacl
     * 2. Check to see if the entity has a parent which has a dacl (recurrsive)
     * 3. If there is no parent dacl, then use the dacl for the object type
     *
     * @param EntityInterface $entity
     * @param UserEntity $user
     * @return Dacl Access control list
     */
    public function getForEntity(EntityInterface $entity, UserEntity $user): ?Dacl
    {
        $entityDacl = null;
        $daclData = $entity->getValue("dacl");
        if (!empty($daclData)) {
            $decoded = json_decode($daclData, true);
            if ($decoded !== false) {
                $entityDacl = new Dacl($decoded);
                if ($entityDacl->verifyDaclData()) {
                    return $entityDacl;
                }
            }
        }

        // Check to see if the entity type has a parent
        $parentDacl = $this->getForParentEntity($entity, $user);
        if ($parentDacl) {
            return $parentDacl;
        }

        // Now try to get DACL for obj type
        $objDef = $entity->getDefinition();
        // Try to get for from the object definition if permissions have been customized
        if (!empty($objDef->getDacl())) {
            return $objDef->getDacl();
        }

        // If none is found, return a default where admin and creator owner has access only
        return $this->createDefaultDacl($objDef);
    }

    /**
     * Walk up a tree of parent entities to see if any of them have a dacl
     *
     * @param EntityInterface $entity
     * @param UserEntity $user
     * @return Dacl|null
     */
    private function getForParentEntity(EntityInterface $entity, UserEntity $user): ?Dacl
    {
        // Check to see if the entity type has a parent
        $objDef = $entity->getDefinition();
        $fieldToInheritFrom = $objDef->getFieldToInheritDaclFrom();
        if ($fieldToInheritFrom) {
            if ($entity->getValue($fieldToInheritFrom->getName())) {
                // See if we can retrieve the parent entity
                $inheritableEntity = $this->entityLoader->getEntityById($entity->getValue(
                    $fieldToInheritFrom->getName()
                ), $user->getAccountId());

                if ($inheritableEntity) {
                    $dacl = $this->getForEntity($inheritableEntity, $user);
                    if ($dacl) {
                        return $dacl;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Function that will get the Dacl for entity definition
     *
     * @param EntityDefinition $entityDefinition The entity definition where we will be getting the dacl
     * @return Dacl
     */
    public function getForEntityDefinition(EntityDefinition $entityDefinition)
    {
        if (!empty($entityDefinition->getDacl())) {
            return $entityDefinition->getDacl();
        }

        // If none is found, return a default where admin and creator owner has access only
        return $this->createDefaultDacl($entityDefinition);
    }

    /**
     * Private function that creates a default entries of Dacl
     *
     * @param EntityDefinition $entityDefinition
     * @return Dacl
     */
    private function createDefaultDacl(EntityDefinition $entityDefinition)
    {
        $userGroups = $this->groupingLoader->get(
            ObjectTypes::USER . '/groups',
            $entityDefinition->getAccountId()
        );
        $default = new Dacl();
        $groupAdmin = $userGroups->getByName(UserEntity::GROUP_ADMINISTRATORS);
        $default->allowGroup($groupAdmin->getGroupId(), Dacl::PERM_FULL);
        $groupCreator = $userGroups->getByName(UserEntity::GROUP_CREATOROWNER);
        $default->allowGroup($groupCreator->getGroupId(), Dacl::PERM_FULL);

        // There's a few special cases where we want to default to giving all users
        // access to view the entity in question
        if (
            $entityDefinition->getObjType() === ObjectTypes::USER ||
            $entityDefinition->getObjType() === ObjectTypes::STATUS_UPDATE
        ) {
            $groupUsers = $userGroups->getByName(UserEntity::GROUP_USERS);
            $default->allowGroup($groupUsers->getGroupId(), Dacl::PERM_VIEW);
        }

        return $default;
    }
}
