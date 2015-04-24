<?php
/**
 * Provide user extensions to base Entity class
 * 
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2014 Aereus
 */

namespace Netric\Entity\ObjType;

use Netric\Authentication\AuthenticationService;

/**
 * Description of User
 *
 * @author Sky Stebnicki
 */
class User extends \Netric\Entity implements \Netric\Entity\EntityInterface
{
    const USER_ADMINISTRATOR = -1;
    const USER_CURRENT = -3;
    const USER_ANONYMOUS = -4;
    const USER_SYSTEM = -5;
    const USER_WORKFLOW = -6;
    //put your code here
    
    const GROUP_USERS = -4;
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
    }

    /**
     * Callback function used for derrived subclasses
     *
     * @param \Netric\ServiceManager\ServiceLocatorInterface $sm Service manager used to load supporting services
     */
    public function onAfterSave(\Netric\ServiceManager\ServiceLocatorInterface $sm)
    {
        
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
}
