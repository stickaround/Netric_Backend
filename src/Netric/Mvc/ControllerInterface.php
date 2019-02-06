<?php
namespace Netric\Mvc;

use Netric\Permissions\Dacl;

/**
 * We implement this just to assure only controllers are being returned from factories
 */
interface ControllerInterface
{
    /**
     * Determine what users can access actions in the concrete controller
     *
     * This can easily be overridden in derived controllers to allow custom access per
     * controller or each action can handle its own access control list if desired.
     *
     * @return Dacl
     */
    public function getAccessControlList(): Dacl;
}
