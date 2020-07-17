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
use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\Permissions\DaclLoaderFactory;
use Netric\Permissions\Dacl;
use Netric\EntityDefinition\EntityDefinition;
use Netric\Authentication\AuthenticationServiceFactory;

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
    const USER_ADMINISTRATOR = '935b5810-831d-11e8-adc0-fa7ae01bbebc';
    const USER_CURRENT = '935b5cb6-831d-11e8-adc0-fa7ae01bbebc';
    const USER_ANONYMOUS = '935b5e28-831d-11e8-adc0-fa7ae01bbebc';
    const USER_SYSTEM = '935b5f54-831d-11e8-adc0-fa7ae01bbebc';
    const USER_WORKFLOW = '935b6076-831d-11e8-adc0-fa7ae01bbebc';

    /**
     * System groups
     *
     * @const int
     */
    const GROUP_USERS = -4; // Authenticated users
    const GROUP_EVERYONE = -3;
    const GROUP_CREATOROWNER = -2;
    const GROUP_ADMINISTRATORS = -1;

    /**
     * The loader for a specific entity
     *
     * @var EntityLoader
     */
    private $entityLoader = null;

    /**
     * Class constructor
     *
     * @param EntityDefinition $def The definition of this type of object
     * @param EntityLoader $entityLoader The loader for a specific entity
     */
    public function __construct(EntityDefinition $def, EntityLoader $entityLoader)
    {
        parent::__construct($def, $entityLoader);

        $this->entityLoader = $entityLoader;
    }

    /**
     * Callback function used for derrived subclasses
     *
     * @param AccountServiceManagerInterface $sm Service manager used to load supporting services
     */
    public function onBeforeSave(AccountServiceManagerInterface $sm)
    {
        // If the password was updated for this user then encrypt it
        if ($this->fieldValueChanged("password")) {
            $authService = $sm->get(AuthenticationServiceFactory::class);
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
     * @param AccountServiceManagerInterface $sm Service manager used to load supporting services
     */
    public function onAfterSave(AccountServiceManagerInterface $sm)
    {
        // Update the account email address for the application if changed
        if ($this->fieldValueChanged("email") || $this->fieldValueChanged("name")) {
            // Delete old username if changed
            $previousName = $this->getPreviousValue("name");
            if ($previousName && $previousName != $this->getValue("name")) {
                $sm->getAccount()->setAccountUserEmail($previousName, null);
            }

            // Set the new username to this email address
            $sm->getAccount()->setAccountUserEmail(
                $this->getValue("name"),
                $this->getValue("email")
            );
        }
    }

    /**
     * Callback function used for derrived subclasses and called just before a hard delete occurs
     *
     * @param AccountServiceManagerInterface $sm Service manager used to load supporting services
     */
    public function onBeforeDeleteHard(AccountServiceManagerInterface $sm)
    {
        /*
         * Delete any dangling of this user to any email addresses since that is used
         * for universal login when someone users their email address to log in to multiple accounts
         */
        $sm->getAccount()->setAccountUserEmail($this->getValue("name"), null);
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
            $salt = $authService->generateSalt();
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

        // Add to authenticated users group if we have determined this is a valid user
        if ($this->getEntityId() &&  !$this->isAnonymous() && !in_array(self::GROUP_USERS, $groups)) {
            $groups[] = self::GROUP_USERS;
        }

        // Of course every user is part of everyone
        if (!in_array(self::GROUP_EVERYONE, $groups)) {
            $groups[] = self::GROUP_EVERYONE;
        }

        return $groups;
    }

    /**
     * Determine if this is anonymous
     */
    public function isAnonymous()
    {
        return ($this->getValue('entity_id') == self::USER_ANONYMOUS);
    }

    /**
     * Determine if this is a system user
     */
    public function isSystem()
    {
        return ($this->getValue('entity_id') == self::USER_SYSTEM);
    }

    /**
     * Set whether or not this user is an administrator
     *
     * @param bool $isAdmin Flag to indicate if user is an administrator
     */
    public function setIsAdmin($isAdmin = true)
    {
        if ($isAdmin) {
            $this->addMultiValue("groups", self::GROUP_ADMINISTRATORS, "Administrators");
        } else {
            $this->removeMultiValue("groups", self::GROUP_ADMINISTRATORS);
        }
    }

    /**
     * Check if this is an admin account
     *
     * @return bool
     */
    public function isAdmin()
    {
        $groups = $this->getGroups();
        foreach ($groups as $group) {
            if ($group == self::GROUP_ADMINISTRATORS) {
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
     * Override getOwnerGuid to always return $this->id for a user entity
     *
     * We do this because a user is always the owner of him or her self in
     * terms of permissions and/or delegation of responsibility.
     *
     * @return int
     */
    public function getOwnerGuid()
    {
        if ($this->getEntityId()) {
            return $this->getEntityId();
        }

        return parent::getOwnerGuid();
    }
}
