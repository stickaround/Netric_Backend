<?php
/**
 * Controller for account interactoin
 */
namespace Netric\Controller;

use \Netric\Mvc;

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
		$moduleService = $serviceManager->get("Netric/Account/Module/ModuleService");

		// Get the current user
		$user = $this->account->getUser();

		// Get the modules specific for the current user
		$modules = $moduleService->getForUser($user);


		$modulesWitNaviation = array();
		foreach($modules as $module) {

			// Get the additional module details (e.g. default_route, naviagtion links, and nav icon)
			$newModule = $moduleService->getAdditionalModuleInfo($module);

			if($newModule->getNavigation())
				$modulesWitNaviation[] = $newModule->toArray();
		}

		// If the current user is an Admin, then let's include the settings module
		if ($user->isAdmin())
		{
			$modulesWitNaviation[] = $moduleService->getSettingsModule()->toArray();
		}


		// Setup the return details
		$ret = array(
			"id" => $this->account->getId(),
			"name" => $this->account->getName(),
			"orgName" => "", // TODO: $this->account->get
			"defaultModule" => "notes", // TODO: this should be home until it is configurable
			"modules" => $modulesWitNaviation
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
