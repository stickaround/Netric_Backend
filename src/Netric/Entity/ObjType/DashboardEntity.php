<?php

/**
 * Dashboard entity extension
 *
 * @author Marl Tumulak <marl.tumulak@aereus.com>
 * @copyright 2018 Aereus
 */

namespace Netric\Entity\ObjType;

use Netric\Entity\Entity;
use Netric\Entity\EntityInterface;
use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\Permissions\DaclLoaderFactory;
use Netric\Entity\ObjType\UserEntity;
use Netric\Permissions\Dacl;

/**
 * Activty entity used for logging activity logs
 */
class DashboardEntity extends Entity implements EntityInterface
{
    /**
     * Callback function used for derrived subclasses
     *
     * @param AccountServiceManagerInterface $sm Service manager used to load supporting services
     */
    public function onBeforeSave(AccountServiceManagerInterface $sm)
    {
        // If we are dealing with a system-wide dashboard, then we will set everyone to have a view permission
        if ($this->getvalue("scope") === "system") {
            $daclLoader = $sm->get(DaclLoaderFactory::class);
            $dacl = $daclLoader->getForEntity($this);

            $dacl->allowGroup(UserEntity::GROUP_EVERYONE, DACL::PERM_VIEW);
            $this->setValue("dacl", json_encode($dacl->toArray()));
        }
    }

    /**
     * Get the encoded object reference for this entity
     *
     * @param bool $includeName If true then name will be encoded with the reference
     * @return string [obj_type]:[id]:[name]
     */
    public function getObjRef($includeName = false)
    {
        $objType = $this->def->getObjType();
        $name = $this->getValue("widget_name");

        return self::encodeObjRef($objType, $this->getId(), $name);
    }
}
