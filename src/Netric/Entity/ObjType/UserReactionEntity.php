<?php

/**
 * Provide user extensions to base Entity class
 *
 * @author Marl Tumulak <marl@aereus.com>
 * @copyright 2021 Aereus
 */

namespace Netric\Entity\ObjType;

use Netric\Authentication\AuthenticationService;
use Netric\Entity\Entity;
use Netric\Entity\EntityInterface;
use Netric\Entity\EntityLoader;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\EntityDefinition\EntityDefinition;
use Netric\Authentication\AuthenticationServiceFactory;
use Netric\EntityDefinition\ObjectTypes;
use Netric\EntityGroupings\GroupingLoader;
use Netric\Account\AccountContainerInterface;
use Netric\Permissions\Dacl;

/**
 * Description of User Reaction
 *
 * @author Marl Tumulak
 */
class UserReactionEntity extends Entity implements EntityInterface
{
    /**
     * The loader for a specific entity
     *
     * @var EntityLoader
     */
    private $entityLoader = null;

    /**
     * Grouping loader used to get user groups
     *
     * @var GroupingLoader
     */
    private $groupingLoader = null;

    /**
     * Container used to load accounts
     */
    private AccountContainerInterface $accountContainer;

    /**
     * Class constructor
     *
     * @param EntityDefinition $def The definition of this type of object
     * @param EntityLoader $entityLoader The loader for a specific entity
     * @param GroupingLoader $groupingLoader Handles the loading and saving of groupings
     * @param AccountContainerInterface $accountContainer Container used to load accounts
     */
    public function __construct(
        EntityDefinition $def,
        EntityLoader $entityLoader,
        GroupingLoader $groupingLoader,
        AccountContainerInterface $accountContainer
    ) {
        $this->entityLoader = $entityLoader;
        $this->groupingLoader = $groupingLoader;
        $this->accountContainer = $accountContainer;

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
        if (!$this->fieldValueChanged('reaction')) {
            return;
        }

        $userGroups = $this->groupingLoader->get(ObjectTypes::USER . '/groups', $user->getAccountId());
        $groupAdmin = $userGroups->getByName(UserEntity::GROUP_ADMINISTRATORS);
        $groupCreator = $userGroups->getByName(UserEntity::GROUP_CREATOROWNER);

        $objReference = $this->getValue("obj_reference");
        $entityReactedOn = $this->entityLoader->getEntityById($objReference, $user->getAccountId());

        // Set reaction associations to all directly associated objects if new
        if ($entityReactedOn) {
            // Update the num_reactions field of the entity we are reacting on
            // Only if the comment is new and/or just deleted 
            if ($this->getValue('revision') <= 1 || ($this->isArchived() && $this->fieldValueChanged('f_deleted'))) {
                // Determine if we should increment or decrement
                $added = ($this->isArchived()) ? false : true;
                $entityReactedOn->setHasReaction($added);
            }

            // Add object references to the list of associations
            $this->addMultiValue("associations", $entityReactedOn->getEntityId(), $entityReactedOn->getName());

            // Make sure followers of this entity reacted on are synchronized with the entity
            $this->syncFollowers($entityReactedOn);

            // Save the entity we are reacting on if there were changes
            if ($entityReactedOn->isDirty()) {
                $this->entityLoader->save($entityReactedOn, $user);
            }

            // Make sure all members have view access to the user reaction
            $dacl = new Dacl();
            $members = $entityReactedOn->getValue('followers');
            $membersName = [];
            foreach ($members as $userId) {
                $dacl->allowUser($userId, Dacl::PERM_VIEW);
            }

            // Make sure the owner has full control
            $dacl->allowGroup($groupCreator->getGroupId(), Dacl::PERM_FULL);

            // Save custom permissions
            $this->setValue('dacl', json_encode($dacl->toArray()));
        }
    }
}
