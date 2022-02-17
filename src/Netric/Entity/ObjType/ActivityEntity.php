<?php

/**
 * Activity entity extension
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */

namespace Netric\Entity\ObjType;

use Netric\Entity\Entity;
use Netric\Entity\EntityInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Entity\ObjType\UserEntity;

/**
 * Activty entity used for logging activity logs
 */
class ActivityEntity extends Entity implements EntityInterface
{
    /**
     * Callback function used for derrived subclasses
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @param UserEntity $user The user that is acting on this entity
     */
    public function onBeforeSave(ServiceLocatorInterface $serviceLocator, UserEntity $user)
    {
        // Set association for the object which is used for queries
        if ($this->getValue('obj_reference')) {
            $objRef = $this->getValue('obj_reference');
            if ($objRef) {
                $this->addMultiValue(
                    "associations",
                    $objRef,
                    $this->getValueName('obj_reference')
                );
            }
        }

        // Make sure the required data is set
        if (
            empty($this->getValue("subject")) ||
            empty($this->getValue("verb"))
        ) {
            throw new \InvalidArgumentException(
                "subject and verb are required: " .
                    $this->getValue("subject") . "," .
                    $this->getValue("verb") . "," .
                    var_export($this->toArray(), true)
            );
        }
    }
}
