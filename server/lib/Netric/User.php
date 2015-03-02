<?php
/*
 * Extend entity to support user functions
 * 
 * NOTE: All functionality should be put in Entity\User rather than here.
 * The purpose of this class is simply to serve as a factory that takes an
 * account param and an optional id and constructs a user.
 * 
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2014 Aereus
 */

namespace Netric;

/**
 * Netric user class is basically just a factory for Entity\User
 */
class User extends Entity\ObjType\User
{
    /**
     * Class constructor
     * 
     * @param \Netric\Account $account The current tennant account
     * @param string $id Optional id of user to load
     */
    public function __construct(&$account, $id="") 
    {
        $defLoader = $account->getServiceManager()->get("EntityDefinitionLoader");
		$def = $defLoader->get("user");
        parent::__construct($def);
        
        // Load from datamapper if id is defined
        if ($id)
        {
            $loader = $account->getServiceManager()->get("EntityLoader");
            // TODO: this is pretty much broken
            $user = $loader->get("user", $id);
        }
    }
}