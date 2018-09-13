<?php

/**
 * Controller for account interaction
 */
namespace Netric\Controller;

use Netric\Mvc;
use Netric\Account\Module\ModuleServiceFactory;

class AccountController extends Mvc\AbstractAccountController
{
    /**
     * Get the definition of an account
     */
    public function getGetAction()
    {
        // Get the service manager of the current user
        $serviceManager = $this->account->getServiceManager();

        // Load the Module Service
        $moduleService = $serviceManager->get(ModuleServiceFactory::class);

        // Get the current user
        $user = $this->account->getUser();

        // Get the modules specific for the current user
        // NOTE: This will only retrieve modules that either have a custom
        // navigation defined in the database OR can be found in /data/modules/...
        $userModules = $moduleService->getForUser($user);

        $modules = array();

        // Loop through each module for the current user
        foreach ($userModules as $module) {
            // Convert the Module object into an array
            $modules[] = $module->toArray();
        }

        // Setup the return details
        $ret = array(
            "id" => $this->account->getId(),
            "name" => $this->account->getName(),
            "orgName" => "", // TODO: $this->account->get
            "defaultModule" => "home", // TODO: this should be home until it is configurable
            "modules" => $modules
        );

        return $this->sendOutput($ret);
    }

    /**
     * Just in case they use POST
     */
    public function postGetAction()
    {
        return $this->getGetAction();
    }
}
