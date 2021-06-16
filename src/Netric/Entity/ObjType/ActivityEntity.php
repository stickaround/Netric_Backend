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
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Entity\ObjType\UserEntity;
use Netric\Entity\EntityLoader;
use Netric\EntityDefinition\EntityDefinition;

/**
 * Activty entity used for logging activity logs
 */
class ActivityEntity extends Entity implements EntityInterface
{
    /**
     * Verbs
     *
     * @const int
     */
    const VERB_CREATED = 'created';
    const VERB_UPDATED = 'updated';
    const VERB_DELETED = 'deleted';
    const VERB_READ = 'read';
    const VERB_SHARED = 'shared';
    const VERB_SENT = 'sent';
    const VERB_COMPLETED = 'completed';
    const VERB_APPROVED = 'approved';

    /**
     * Class constructor
     *
     * @param EntityDefinition $def The definition of this type of object
     * @param EntityLoader $entityLoader The loader for a specific entity
     */
    public function __construct(EntityDefinition $def, EntityLoader $entityLoader)
    {
        parent::__construct($def, $entityLoader);
    }

    /**
     * Callback function used for derrived subclasses
     *
     * @param ServiceContainerInterface $serviceLocator ServiceLocator for injecting dependencies
     * @param UserEntity $user The user that is acting on this entity
     */
    public function onBeforeSave(ServiceContainerInterface $serviceLocator, UserEntity $user)
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
        if (empty($this->getValue("subject")) ||
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
