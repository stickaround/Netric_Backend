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
use Netric\Entity\EntityLoader;
use Netric\EntityDefinition\EntityDefinition;
use Netric\Permissions\Dacl;

/**
 * Entity for chat rooms
 */
class ChatRoomEntity extends Entity implements EntityInterface
{
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
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @param UserEntity $user The user that is acting on this entity
     */
    public function onBeforeSave(ServiceLocatorInterface $serviceLocator, UserEntity $user)
    {
        if (!$this->fieldValueChanged('members')) {
            return;
        }

        // Make sure all members have view access to the room
        $dacl = new Dacl();
        $members = $this->getValue('members');
        foreach ($members as $userId) {
            $dacl->allowUser($userId, Dacl::PERM_VIEW);
        }

        // Make sure the owner has full control
        $dacl->allowGroup(UserEntity::GROUP_CREATOROWNER, Dacl::PERM_FULL);

        // If this is not a direct message, then add administrators
        if ($this->getValue('scope') === 'channel') {
            $dacl->allowGroup(UserEntity::GROUP_ADMINISTRATORS, Dacl::PERM_FULL);
        }

        // Save custom permissions
        $this->setValue('dacl', json_encode($dacl->toArray()));
    }
}
