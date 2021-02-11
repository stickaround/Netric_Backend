<?php

namespace Netric\Controller;

use Netric\Mvc;
use Netric\Mvc\ControllerInterface;
use Netric\Mvc\AbstractFactoriedController;
use Netric\Account\AccountContainerFactory;
use Netric\Account\AccountContainerInterface;
use Netric\Application\Response\HttpResponse;
use Netric\Request\HttpRequest;
use Netric\Authentication\AuthenticationService;
use Netric\Account\Module\ModuleService;

/**
 * Controller for interacting with entities
 */
class ModuleController extends AbstractFactoriedController implements ControllerInterface
{
    /**
     * Container used to load accounts
     */
    private AccountContainerInterface $accountContainer;

    /**
     * Service used to get the current user/account
     */
    private AuthenticationService $authService;

    /**
     * Service for working with modules
     */
    private ModuleService $moduleService;

    /**
     * Initialize controller and all dependencies
     *
     * @param AccountContainerInterface $accountContainer Container used to load accounts
     * @param AuthenticationService $authService Service used to get the current user/account
     * @param ModuleService $moduleService Service for working with modules
     */
    public function __construct(
        AccountContainerInterface $accountContainer,
        AuthenticationService $authService,
        ModuleService $moduleService
    ) {
        $this->accountContainer = $accountContainer;
        $this->authService = $authService;
        $this->moduleService = $moduleService;
    }

    /**
     * Get the currently authenticated account
     *
     * @return Account
     */
    private function getAuthenticatedAccount()
    {
        $authIdentity = $this->authService->getIdentity();
        if (!$authIdentity) {
            return null;
        }

        return $this->accountContainer->loadById($authIdentity->getAccountId());
    }

    /**
     * Get the definition of an account
     *
     * @param HttpRequest $request Request object for this run
     * @return HttpResponse
     */
    public function getGetAction(HttpRequest $request): HttpResponse
    {
        $response = new HttpResponse($request);

        $moduleName = $request->getParam('moduleName');
        if (!$moduleName) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "moduleName is a required query param."]);
            return $response;
        }

        // Make sure that we have an authenticated account
        $currentAccount = $this->getAuthenticatedAccount();
        if (!$currentAccount) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "Account authentication error."]);
            return $response;
        }

        $module = $this->moduleService->getByName($moduleName, $currentAccount->getAccountId());
        $response->write($module->toArray());
        return $response;
    }

    /**
     * PUT pass-through for save
     *
     * @param HttpRequest $request Request object for this run
     * @return HttpResponse
     */
    public function putSaveAction(HttpRequest $request): HttpResponse
    {
        return $this->postSaveAction($request);
    }

    /**
     * Save the module
     *
     * @param HttpRequest $request Request object for this run
     * @return HttpResponse
     */
    public function postSaveAction(HttpRequest $request): HttpResponse
    {
        $rawBody = $request->getBody();
        $response = new HttpResponse($request);

        if (!$rawBody) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write("Request input is not valid");
            return $response;
        }

        // Decode the json structure
        $objData = json_decode($rawBody, true);

        if (!isset($objData['name'])) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "name is a required param."]);
            return $response;
        }

        // Make sure that we have an authenticated account
        $currentAccount = $this->getAuthenticatedAccount();
        if (!$currentAccount) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "Account authentication error."]);
            return $response;
        }

        $module = $this->moduleService->createNewModule();
        if (isset($objData["id"]) && $objData["id"]) {
            $module->setModuleId($objData["id"]);
        }

        // Import the module data
        $module->fromArray($objData);
        $module->setDirty(true);

        if ($this->moduleService->save($module, $currentAccount->getAccountId())) {
            // Return the saved module
            $response->write($module->toArray());
            return $response;
        }

        $response->setReturnCode(HttpResponse::STATUS_INTERNAL_SERVER_ERROR);
        $response->write(["error" => "Error saving the module."]);
        return $response;
    }

    /**
     * PUT pass-through for delete
     *
     * @param HttpRequest $request Request object for this run
     * @return HttpResponse
     */
    public function putDeleteAction(HttpRequest $request): HttpResponse
    {
        return $this->postDeleteAction($request);
    }

    /**
     * Delete the module
     *
     * @param HttpRequest $request Request object for this run
     * @return HttpResponse
     */
    public function postDeleteAction(HttpRequest $request): HttpResponse
    {
        $response = new HttpResponse($request);
        $moduleId = $request->getParam("id");

        // Check if the request was sent as a json object
        $rawBody = $request->getBody();
        if ($rawBody) {
            $body = json_decode($rawBody, true);
            $moduleId = $body['id'];
        }

        // Check if we have module id
        if (!$moduleId) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "id is a required param."]);
            return $response;
        }

        // Make sure that we have an authenticated account
        $currentAccount = $this->getAuthenticatedAccount();
        if (!$currentAccount) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "Account authentication error."]);
            return $response;
        }

        $module = $this->moduleService->getById($moduleId, $currentAccount->getAccountId());
        if ($this->moduleService->delete($module)) {
            // Return true since we have successfully deleted the module
            $response->write(true);
            return $response;
        }

        $response->setReturnCode(HttpResponse::STATUS_INTERNAL_SERVER_ERROR);
        $response->write(["error" => "Error while trying to delete the module."]);
        return $response;
    }

    /**
     * Get the available module of an account
     *
     * @param HttpRequest $request Request object for this run
     * @return HttpResponse
     */
    public function getGetAvailableModulesAction(HttpRequest $request): HttpResponse
    {
        $response = new HttpResponse($request);

        // Make sure that we have an authenticated account
        $currentAccount = $this->getAuthenticatedAccount();
        if (!$currentAccount) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "Account authentication error."]);
            return $response;
        }

        // Get the available modules for the current user
        $userModules = $this->moduleService->getForUser($currentAccount->getAuthenticatedUser());

        // Loop through each module for the current user
        $modules = [];
        foreach ($userModules as $module) {
            // Convert the Module object into an array
            $modules[] = $module->toArray();
        }

        $response->write($modules);
        return $response;
    }
}
