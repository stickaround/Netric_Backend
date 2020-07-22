<?php

namespace Netric\Mvc;

use Netric\Permissions\Dacl;
use Netric\Entity\ObjType\UserEntity;

/**
 * Main abstract class for controllers using a factory (newer) in netric
 *
 * This should eventually replace AbstractController
 */
abstract class AbstractFactoriedController
{
    /**
     * Determine what users can access actions in the concrete controller
     *
     * This can easily be overridden in derived controllers to allow custom access per
     * controller or each action can handle its own access control list if desired.
     *
     * @return Dacl
     */
    public function getAccessControlList(): Dacl
    {
        $dacl = new Dacl();

        // By default allow everyone to access this controller and let the controller/action handle permissions
        $dacl->allowEveryone();

        return $dacl;
    }
}
