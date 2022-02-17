<?php

namespace Netric\Entity\ObjType;

use Netric\Entity\Entity;
use Netric\Entity\EntityInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Entity\ObjType\UserEntity;

/**
 * Entity for chat rooms
 */
class ChatMessageEntity extends Entity implements EntityInterface
{
    /**
     * Callback function used for derrived subclasses
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @param UserEntity $user The user that is acting on this entity
     */
    public function onBeforeSave(ServiceLocatorInterface $serviceLocator, UserEntity $user)
    {
        if (empty($this->getValue('chat_room'))) {
            return;
        }

        $chatRoom = $this->getEntityLoader()->getEntityById($this->getValue('chat_room'), $this->getAccountId());

        $members = $chatRoom->getValue('members');
        foreach ($members as $memberId) {
            // Add everyone but the sender of the message
            if ($memberId !== $this->getOwnerId()) {
                $this->addMultiValue('to', $memberId);
            }
        }
    }
}
