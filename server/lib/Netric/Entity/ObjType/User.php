<?php
/**
 * Provide user extensions to base Entity class
 * 
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2014 Aereus
 */

namespace Netric\Entity\ObjType;

use DoctrineTest\InstantiatorTestAsset\ExceptionAsset;
use Netric\Authentication\AuthenticationService;
use Netric\Entity\Entity;
use Netric\Entity\EntityInterface;

/**
 * Description of User
 *
 * @author Sky Stebnicki
 */
class User extends Entity implements EntityInterface
{
    /**
     * System users
     *
     * @const int
     */
    const USER_ADMINISTRATOR = -1;
    const USER_CURRENT = -3;
    const USER_ANONYMOUS = -4;
    const USER_SYSTEM = -5;
    const USER_WORKFLOW = -6;
    

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
     * Callback function used for derrived subclasses
     *
     * @param \Netric\ServiceManager\ServiceLocatorInterface $sm Service manager used to load supporting services
     */
    public function onBeforeSave(\Netric\ServiceManager\ServiceLocatorInterface $sm)
    {
        // If the password was updated for this user then encrypt it
        if ($this->fieldValueChanged("password"))
        {
            $authService = $sm->get("/Netric/Authentication/AuthenticationService");
            $this->encryptPassword($authService);
        }

        // Check to see if the username is an email and copy to email if empty
        if (!$this->getValue("email") && strpos($this->getValue("name"), "@"))
        {
            $this->setValue("email", $this->getValue("name"));
        }
    }


    /**
     * Callback function used for derrived subclasses
     *
     * @param \Netric\ServiceManager\ServiceLocatorInterface $sm Service manager used to load supporting services
     */
    public function onAfterSave(\Netric\ServiceManager\ServiceLocatorInterface $sm)
    {
        // Update the account email address for the application if changed
        if ($this->fieldValueChanged("email") || $this->fieldValueChanged("name"))
        {
            // Delete old username if changed
            $previousName = $this->getPreviousValue("name");
            if ($previousName && $previousName != $this->getValue("name"))
            {
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
     * Set clear text password
     *
     * This will generate a salt and encrypt the password
     *
     * @param Netric\Authetication\AuthenticationService $authService For encryption of passwords and salt generation
     */
    private function encryptPassword(AuthenticationService $authService)
    {
        $salt = $this->getValue("password_salt");

        // Check for salt and create if missing
        if (!$salt)
        {
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
        if (!$groups || !is_array($groups))
            $groups = array();

        // Add to authenticated users group if we have determined this is a valid user
        if ($this->getId() &&  !$this->isAnonymous() && !in_array(self::GROUP_USERS, $groups))
            $groups[] = self::GROUP_USERS;

        // Of course every user is part of everyone
        if (!in_array(self::GROUP_EVERYONE, $groups))
            $groups[] = self::GROUP_EVERYONE;

        return $groups;
    }

    /**
     * Determine if this is anonymous
     */
    public function isAnonymous()
    {
        return ($this->getId() == self::USER_ANONYMOUS);
    }
}
