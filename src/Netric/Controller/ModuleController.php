<?php
namespace Netric\Controller;

use \Netric\Mvc;
use Netric\Account\Module\ModuleServiceFactory;

/**
 * Controller for account interactoin
 */
class ModuleController extends Mvc\AbstractAccountController
{
	/**
	 * Get the definition of an account
	 */
	public function getGetAction()
	{
		$params = $this->getRequest()->getParams();

		if (!isset($params['moduleName'])) {
			return $this->sendOutput(['error' => "moduleName is a required query param"]);
		}

		// Get the service manager of the current user
		$serviceManager = $this->account->getServiceManager();

        // Load the Module Service
		$moduleService = $serviceManager->get(ModuleServiceFactory);

		$module = $moduleService->getByName($params['moduleName']);

		return $this->sendOutput($module->toArray());
	}
}
