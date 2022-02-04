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
use Netric\EntityDefinition\EntityDefinition;
use Netric\EntityDefinition\ObjectTypes;
use Netric\EntityGroupings\GroupingLoaderFactory;
use Netric\Permissions\Dacl;

/**
 * Entity for chat rooms
 */
class ChatRoomEntity extends Entity implements EntityInterface
{
    /**
     * System scope
     *
     * @const string
     */
    const ROOM_CHANNEL = 'channel';
    const ROOM_DIRECT = 'direct';

    /**
     * Class constructor
     *
     * @param EntityDefinition $def The definition of this type of object
     */
    public function __construct(EntityDefinition $def)
    {
        parent::__construct($def);
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

        $groupingLoader = $serviceLocator->get(GroupingLoaderFactory::class);
        $userGroups = $groupingLoader->get(ObjectTypes::USER . '/groups', $user->getAccountId());
        $groupAdmin = $userGroups->getByName(UserEntity::GROUP_ADMINISTRATORS);
        $groupCreator = $userGroups->getByName(UserEntity::GROUP_CREATOROWNER);

        // Make sure all members have view and edit access to the room
        $dacl = new Dacl();
        $this->addMultiValue('members', $user->getEntityId(), $user->getName());
        $members = $this->getValue('members');
        $membersName = [];
        foreach ($members as $userId) {
            $dacl->allowUser($userId, Dacl::PERM_VIEW);            
            $membersName[] = $this->getValueName('members', $userId);
        }

        // Make sure the owner has full control
        $dacl->allowGroup($groupCreator->getGroupId(), Dacl::PERM_FULL);

        // If this is not a direct message, then add administrators
        if ($this->getValue('scope') === self::ROOM_CHANNEL) {
            $dacl->allowGroup($groupAdmin->getGroupId(), Dacl::PERM_FULL);
        }

        // If this room has no subject, then set the member names as the subject
        if (empty($this->getValue('subject'))) {
            $this->setValue('subject', implode(", ", $membersName));
        }

        // Save custom permissions
        $this->setValue('dacl', json_encode($dacl->toArray()));
    }

    /**
     * Callback function used for derrived subclasses
     * 
     * @param UserEntity $user The user that is acting on this entity
     */
    public function onGetAppliedName(UserEntity $user)
    {
        // If this is a room channel and subject is set, then just return the subject
        if ($this->getValue('scope') === self::ROOM_CHANNEL && !empty($this->getValue('subject'))) {
            return $this->getValue('subject');
        }

        $members = $this->getValue('members');
        $membersName = [];
        foreach ($members as $userId) {
            if ($userId != $user->getEntityId()) {
                $membersName[] = $this->getValueName('members', $userId);
            }
        }

        $appliedName = implode(", ", $membersName);

        return $appliedName ? $appliedName : "<Empty Room>";
    }
}
