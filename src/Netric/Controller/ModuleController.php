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
        $moduleService = $serviceManager->get(ModuleServiceFactory::class);

        $module = $moduleService->getByName($params['moduleName']);

        return $this->sendOutput($module->toArray());
    }

    /**
     * PUT pass-through for save
     */
    public function putSaveAction()
    {
        return $this->postSaveAction();
    }

    /**
     * Save the module
     */
    public function postSaveAction()
    {
        $rawBody = $this->getRequest()->getBody();

        $ret = array();
        if (!$rawBody) {
            return $this->sendOutput(array("error" => "Request input is not valid"));
        }

        // Decode the json structure
        $objData = json_decode($rawBody, true);

        if (!isset($objData['name'])) {
            return $this->sendOutput(array("error" => "name is a required param"));
        }

        // Get the service manager of the current user
        $serviceManager = $this->account->getServiceManager();
        $moduleService = $serviceManager->get(ModuleServiceFactory::class);

        if (isset($objData["id"]) && $objData["id"])
            $module = $moduleService->getById($objData["id"]);
        else
            $module = $moduleService->createNew();

        // Before setting the updated module data, we need to reset the values to make sure we can update the empty fields
        $module->resetDefaultValues();

        $module->fromArray($objData);
        $module->setDirty(true);

        if ($moduleService->save($module)) {
            // Update the foreign values of the module in case we updated the user id or team id
            $moduleService->updateForeignValues($module);

            // Return the saved module
            return $this->sendOutput($module->toArray());
        } else {
            return $this->sendOutput(array("error" => "Error saving the module"));
        }
    }

    /**
     * PUT pass-through for delete
     */
    public function putDeleteAction()
    {
        return $this->postDeleteAction();
    }

    /**
     * Delete the module
     */
    public function postDeleteAction()
    {
        $id = $this->request->getParam("id");
        if (!$id) {
            return $this->sendOutput(array("error" => "id is a required param"));
        }

        // Get the service manager of the current user
        $serviceManager = $this->account->getServiceManager();
        $moduleService = $serviceManager->get(ModuleServiceFactory::class);

        $module = $moduleService->getById($id);

        if ($moduleService->delete($module)) {
            // Return the saved module
            return $this->sendOutput(true);
        } else {
            return $this->sendOutput(array("error" => "Error saving the module"));
        }
    }

    /**
     * Get the available module of an account
     */
    public function getGetAvailableModulesAction()
    {
        // Get the service manager of the current user
        $serviceManager = $this->account->getServiceManager();

        // Load the Module Service
        $moduleService = $serviceManager->get(ModuleServiceFactory::class);

        // Get the current user
        $user = $this->account->getUser();

        // Get the available modules for the current user
        $userModules = $moduleService->getForUser($user);

        $modules = array();

        // Loop through each module for the current user
        foreach ($userModules as $module) {
            // Convert the Module object into an array
            $modules[] = $module->toArray();
        }

        return $this->sendOutput($modules);
    }
}
