<?php

/**
 * Provide user extensions to base Entity class
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2014 Aereus
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

/**
 * Description of User
 *
 * @author Sky Stebnicki
 */
class UserEntity extends Entity implements EntityInterface
{
    /**
     * System users
     *
     * @const string
     */
    //const USER_CURRENT = '935b5cb6-831d-11e8-adc0-fa7ae01bbebc';
    const USER_CURRENT = 'current.user';
    //const USER_ANONYMOUS = '935b5e28-831d-11e8-adc0-fa7ae01bbebc';
    const USER_ANONYMOUS = 'anonymous';
    //const USER_SYSTEM = '935b5f54-831d-11e8-adc0-fa7ae01bbebc';
    const USER_SYSTEM = 'system';
    //const USER_WORKFLOW = '935b6076-831d-11e8-adc0-fa7ae01bbebc';
    const USER_WORKFLOW = 'workflow';

    /**
     * System groups
     *
     * @const string
     */
    //const GROUP_USERS = -4; // Authenticated users
    const GROUP_USERS = 'Users';
    //const GROUP_EVERYONE = -3;
    const GROUP_EVERYONE = 'Everyone';
    //const GROUP_CREATOROWNER = -2;
    const GROUP_CREATOROWNER = 'Creator Owner';
    //const GROUP_ADMINISTRATORS = -1;
    const GROUP_ADMINISTRATORS = 'Administrators';

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
        // If the password was updated for this user then encrypt it
        if ($this->fieldValueChanged("password")) {
            $authService = $serviceLocator->get(AuthenticationServiceFactory::class);
            $this->encryptPassword($authService);
        }

        // Check to see if the username is an email and copy to email if empty
        if (!$this->getValue("email") && strpos($this->getValue("name"), "@")) {
            $this->setValue("email", $this->getValue("name"));
        }
    }


    /**
     * Callback function used for derrived subclasses
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @param UserEntity $user The user that is acting on this entity
     */
    public function onAfterSave(ServiceLocatorInterface $serviceLocator, UserEntity $user)
    {
        // Get the account
        $account = $this->accountContainer->loadById($this->getAccountId());

        // Update the account email address for the application if changed
        if ($this->fieldValueChanged("email") || $this->fieldValueChanged("name")) {
            // Delete old username if changed
            $previousName = $this->getPreviousValue("name");
            if ($previousName && $previousName != $this->getValue("name")) {
                $account->setAccountUserEmail($previousName, null);
            }

            // Set the new username to this email address
            $account->setAccountUserEmail(
                $this->getValue("name"),
                $this->getValue("email")
            );
        }
    }

    /**
     * Callback function used for derrived subclasses and called just before a hard delete occurs
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @param UserEntity $user The user that is acting on this entity
     */
    public function onBeforeDeleteHard(ServiceLocatorInterface $serviceLocator, UserEntity $user)
    {
        // Get the account
        $account = $this->accountContainer->loadById($this->getAccountId());

        /*
         * Delete any dangling of this user to any email addresses since that is used
         * for universal login when someone users their email address to log in to multiple accounts
         */
        $account->setAccountUserEmail($this->getValue("name"), null);
    }

    /**
     * This function is called just before we export entity as data
     *
     * @return void
     */
    public function onBeforeToArray(): void
    {
        // Make sure default groups are set correctly
        $userGroups = $this->groupingLoader->get(ObjectTypes::USER . '/groups', $this->getAccountId());

        // Add to authenticated users group if we have determined this is a valid user
        $groupUser = $userGroups->getByName(self::GROUP_USERS);
        if (
            $this->getEntityId() &&
            !$this->isAnonymous() &&
            !$this->getValueName('groups', $groupUser->getGroupId())
        ) {
            $this->addMultiValue('groups', $groupUser->getGroupId(), 'Users');
        }

        // Of course every user is part of everyone
        $groupEveryone = $userGroups->getByName(self::GROUP_EVERYONE);
        if (
            $this->getEntityId() &&
            !$this->isAnonymous() &&
            !$this->getValueName('groups', $groupEveryone->getGroupId())
        ) {
            $this->addMultiValue('groups', $groupEveryone->getGroupId(), 'Users');
        }
    }

    /**
     * Set clear text password
     *
     * This will generate a salt and encrypt the password
     *
     * @param AuthenticationService $authService For encryption of passwords and salt generation
     */
    private function encryptPassword(AuthenticationService $authService)
    {
        $salt = $this->getValue("password_salt");

        // Check for salt and create if missing
        if (!$salt) {
            $salt = bin2hex(openssl_random_pseudo_bytes(64));
            $this->setValue("password_salt", $salt);
        }

        // Get password for hashing
        $password = $this->getValue("password");

        // Update password to hashed
        $hashedPassword = $authService->hashPassword($password, $salt);
        $this->setValue("password", md5($hashedPassword));
    }

    /**
     * Get list of groups this user belongs to
     *
     * @return int[]
     */
    public function getGroups()
    {
        $groups = $this->getValue("groups");
        if (!$groups || !is_array($groups)) {
            $groups = [];
        }

        $userGroups = $this->groupingLoader->get(ObjectTypes::USER . '/groups', $this->getAccountId());

        // Add to authenticated users group if we have determined this is a valid user
        $groupUser = $userGroups->getByName(self::GROUP_USERS);
        if ($this->getEntityId() &&  !$this->isAnonymous() && !in_array($groupUser->getGroupId(), $groups)) {
            $groups[] = $groupUser->getGroupId();
        }

        // Of course every user is part of everyone
        $groupEveryone = $userGroups->getByName(self::GROUP_EVERYONE);
        if (!in_array($groupEveryone->getGroupId(), $groups)) {
            $groups[] = $groupEveryone->getGroupId();
        }

        return $groups;
    }

    /**
     * Determine if this is anonymous
     */
    public function isAnonymous()
    {
        return ($this->getValue('uname') == self::USER_ANONYMOUS);
    }

    /**
     * Determine if this is a system user
     */
    public function isSystem()
    {
        return ($this->getValue('uname') == self::USER_SYSTEM);
    }

    /**
     * Set whether or not this user is an administrator
     *
     * @param bool $isAdmin Flag to indicate if user is an administrator
     */
    public function setIsAdmin($isAdmin = true)
    {
        $userGroups = $this->groupingLoader->get(ObjectTypes::USER . '/groups', $this->getAccountId());
        $adminGroup = $userGroups->getByName(self::GROUP_ADMINISTRATORS);
        if ($isAdmin) {
            $this->addMultiValue("groups", $adminGroup->getGroupId(), "Administrators");
        } else {
            $this->removeMultiValue("groups", $adminGroup->getGroupId());
        }
    }

    /**
     * Check if this is an admin account
     *
     * @return bool
     */
    public function isAdmin()
    {
        $userGroups = $this->groupingLoader->get(ObjectTypes::USER . '/groups', $this->getAccountId());
        $adminGroup = $userGroups->getByName(self::GROUP_ADMINISTRATORS);
        $groups = $this->getGroups();
        foreach ($groups as $group) {
            if ($group == $adminGroup->getGroupId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the first name of the user
     *
     * @return string
     */
    public function getFirstName()
    {
        $fullName = $this->getValue("full_name");

        if (!$fullName) {
            return null;
        }

        $pos = strpos($fullName, ' ');
        return substr($fullName, 0, $pos);
    }

    /**
     * Get the first name of the user
     *
     * @return string
     */
    public function getLastName()
    {
        $fullName = $this->getValue("full_name");

        if (!$fullName) {
            return null;
        }

        $pos = strpos($fullName, ' ');
        return ($pos !== false) ? substr($fullName, $pos + 1) : null;
    }

    /**
     * Override getOwnerId to always return $this->id for a user entity
     *
     * We do this because a user is always the owner of him or her self in
     * terms of permissions and/or delegation of responsibility.
     *
     * @return int
     */
    public function getOwnerId()
    {
        if ($this->getEntityId()) {
            return $this->getEntityId();
        }

        return parent::getOwnerId();
    }
}
