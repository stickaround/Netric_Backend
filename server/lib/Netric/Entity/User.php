<?php
/*
 * Provide user extensions to base Entity class
 * 
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2014 Aereus
 */

namespace Netric\Entity;

/**
 * Description of User
 *
 * @author Sky Stebnicki
 */
class User extends \Netric\Entity implements \Netric\EntityInterface
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
     * @param \Netric\ServiceManager $sm Service manager used to load supporting services
     */
    public function onBeforeSave(\Netric\ServiceManager $sm)
    {

    }

    /**
     * Callback function used for derrived subclasses
     *
     * @param E\Netric\ServiceManager $sm Service manager used to load supporting services
     */
    public function onAfterSave(\Netric\ServiceManager $sm)
    {
        
    }
}
