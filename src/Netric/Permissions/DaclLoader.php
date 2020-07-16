<?php

/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2016 Aereus
 */

namespace Netric\Permissions;

use Netric\Entity\EntityInterface;
use Netric\EntityDefinition\EntityDefinition;
use Netric\Entity\EntityLoader;
use Netric\Entity\ObjType\UserEntity;

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
     * Class constructor
     *
     * @param EntityLoader $entityLoader The loader for the entity
     */
    public function __construct(EntityLoader $entityLoader)
    {
        $this->entityLoader = $entityLoader;
    }

    /**
     * Get a DACL for an entity
     *
     * 1. Check if the entity has its own dacl
     * 2. Check to see if the entity has a parent which has a dacl (recurrsive)
     * 3. If there is no parent dacl, then use the dacl for the object type
     *
     * @param EntityInterface $entity
     * @param bool $fallBackToObjType If true and no entity dacl is found get dacl for all objects of that type
     * @return Dacl Access control list
     */
    public function getForEntity(EntityInterface $entity, $fallBackToObjType = true)
    {
        $daclData = $entity->getValue("dacl");
        if (!empty($daclData)) {
            $decoded = json_decode($daclData, true);
            if ($decoded !== false) {
                return new Dacl($decoded);
            }
        }

        // Check to see if the entity type has a parent
        $objDef = $entity->getDefinition();
        if ($objDef->parentField) {
            $fieldDef = $objDef->getField($objDef->parentField);
            if ($entity->getValue($objDef->parentField) && $fieldDef->subtype) {
                $parentEntity = $this->entityLoader->getByGuid($entity->getValue($objDef->parentField));
                if ($parentEntity) {
                    $dacl = $this->getForEntity($parentEntity, false);
                    if ($dacl) {
                        return $dacl;
                    }
                }
            }
        }

        // Now try to get DACL for obj type
        if ($fallBackToObjType) {
            // Try to get for from the object definition if permissions have been customized
            if (!empty($objDef->getDacl())) {
                return $objDef->getDacl();
            }

            // If none is found, return a default where admin and creator owner has access only
            return $this->createDefaultDacl();
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
        return $this->createDefaultDacl();
    }

    /**
     * Private function that creates a default entries of Dacl
     *
     * @return Dacl
     */
    private function createDefaultDacl()
    {
        $default = new Dacl();
        $default->allowGroup(UserEntity::GROUP_ADMINISTRATORS, Dacl::PERM_FULL);
        $default->allowGroup(UserEntity::GROUP_CREATOROWNER, Dacl::PERM_FULL);
        return $default;
    }

    /**
     * Get an access controll list by name
     *
     * @param string $key The name of list to pull
     * @return Dacl
     */
    public function byName($key, $cache = true)
    {
        /* Old code... should now get from $this->dm
        $key = $this->dbh->dbname . "/" . $key;

        if (isset($this->dacls[$key]) && $cache)
            return $this->dacls[$key];

        // Not yet loaded, create then store
        if ($cache)
        {
            $this->dacls[$key] = new Dacl($this->dbh, $key);
            return $this->dacls[$key];
        }
        else
        {
            $dacl = new Dacl($this->dbh, $key);
            return $dacl;
        }
         */
    }
}
