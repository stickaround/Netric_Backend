<?php

/**
 * Controller for handling Browser View
 */

namespace Netric\Controller;

use Netric\Mvc;
use Netric\Mvc\ControllerInterface;
use Netric\Mvc\AbstractFactoriedController;
use Netric\Account\AccountContainerFactory;
use Netric\Account\AccountContainerInterface;
use Netric\Application\Response\HttpResponse;
use Netric\Request\HttpRequest;
use Netric\Authentication\AuthenticationService;
use Netric\Entity\BrowserView\BrowserView;
use Netric\Entity\BrowserView\BrowserViewService;

class BrowserViewController extends AbstractFactoriedController implements ControllerInterface
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
     * Manages the entity browser views
     */
    private BrowserViewService $browserViewService;

    /**
     * Initialize controller and all dependencies
     *
     * @param AccountContainerInterface $accountContainer Container used to load accounts
     * @param AuthenticationService $authService Service used to get the current user/account
     * @param BrowserViewService $browserViewService Manages the entity browser views
     */
    public function __construct(
        AccountContainerInterface $accountContainer,
        AuthenticationService $authService,
        BrowserViewService $browserViewService
    ) {
        $this->accountContainer = $accountContainer;
        $this->authService = $authService;
        $this->browserViewService = $browserViewService;
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
     * Save a browser view
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

        if (!isset($objData['obj_type'])) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "obj_type is a required param."]);
            return $response;
        }

        // Make sure that we have an authenticated account
        $currentAccount = $this->getAuthenticatedAccount();
        if (!$currentAccount) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "Account authentication error."]);
            return $response;
        }

        $view = new BrowserView();
        $view->setObjType($objData['obj_type']);
        $view->fromArray($objData);

        try {
            $result = $this->browserViewService->saveView($view, $currentAccount->getAccountId());
        } catch (\RuntimeException $ex) {
            $response->setReturnCode(HttpResponse::STATUS_INTERNAL_SERVER_ERROR);
            $response->write(["error" => "Error saving browser view: " . $ex->getMessage()]);
            return $response;
        }

        if (!$view->isSystem() && $view->isDefault()) {
            $this->browserViewService->setDefaultViewForUser($view->getObjType(), $this->account->getUser(), $result);
        }

        $response->write($result);
        return $response;
    }

    /**
     * Set a default browser view
     */
    public function postSetDefaultViewAction(HttpRequest $request): HttpResponse
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
        if (!isset($objData['obj_type'])) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "obj_type is a required param."]);
            return $response;
        }

        // Make sure that we have an authenticated account
        $currentAccount = $this->getAuthenticatedAccount();
        if (!$currentAccount) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "Account authentication error."]);
            return $response;
        }

        $view = new BrowserView();
        $view->setObjType($objData['obj_type']);
        $view->fromArray($objData);

        if (!$view->getId()) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "Browser View should be saved first before setting as the default view."]);
            return $response;
        }

        $this->browserViewService->setDefaultViewForUser($view->getObjType(), $currentAccount->getAuthenticatedUser(), $view->getId());
        $response->write($view->getId());
        return $response;
    }

    /**
     * Delete the browser view
     */
    public function postDeleteViewAction(HttpRequest $request): HttpResponse
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
        if (!isset($objData['id'])) {
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

        $view = new BrowserView();
        $view->setObjType($objData['obj_type']);
        $view->fromArray($objData);

        if ($this->browserViewService->deleteView($view)) {
            // Return true since we have successfully deleted the browser view
            $response->write(true);
            return $response;
        } else {
            $response->setReturnCode(HttpResponse::STATUS_INTERNAL_SERVER_ERROR);
            $response->write(["error" => "Error while trying to delete the browser view."]);
            return $response;
        }
    }
}
