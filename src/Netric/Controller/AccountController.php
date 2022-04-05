<?php

/**
 * Controller for account interaction
 */

namespace Netric\Controller;

use Netric\Mvc\ControllerInterface;
use Netric\Mvc\AbstractFactoriedController;
use Netric\Account\AccountContainerInterface;
use Netric\Application\Response\HttpResponse;
use Netric\Request\HttpRequest;
use Netric\Authentication\AuthenticationService;
use Netric\Account\Module\ModuleService;
use Netric\Account\Billing\AccountBillingService;
use Netric\Entity\EntityLoader;
use Netric\PaymentGateway\PaymentMethod\CreditCard;
use RuntimeException;

class AccountController extends AbstractFactoriedController implements ControllerInterface
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
     * Service for getting the account biling details
     */
    private AccountBillingService $accountBillingService;

    /**
     * Handles the loading and saving of entities
     */
    private EntityLoader $entityLoader;

    /**
     * Initialize controller and all dependencies
     *
     * @param AccountContainerInterface $accountContainer Container used to load accounts
     * @param AuthenticationService $authService Service used to get the current user/account
     * @param EntityLoader $entityLoader Handles the loading and saving of entities
     * @param ModuleService $moduleService Service for working with modules
     * @param AccountBillingService $accountBillingService Service for getting the account biling details
     */
    public function __construct(
        AccountContainerInterface $accountContainer,
        AuthenticationService $authService,
        EntityLoader $entityLoader,
        ModuleService $moduleService,
        AccountBillingService $accountBillingService
    ) {
        $this->accountContainer = $accountContainer;
        $this->authService = $authService;
        $this->entityLoader = $entityLoader;
        $this->moduleService = $moduleService;
        $this->accountBillingService = $accountBillingService;
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

        // Make sure that we have an authenticated account
        $currentAccount = $this->getAuthenticatedAccount();
        if (!$currentAccount) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "Account authentication error."]);
            return $response;
        }

        // Get the modules specific for the current user
        // NOTE: This will only retrieve modules that either have a custom
        // navigation defined in the database OR can be found in /data/modules/...
        $currentUser = $currentAccount->getAuthenticatedUser();
        $userModules = $this->moduleService->getForUser($currentUser);

        $modules = [];

        // Loop through each module for the current user
        foreach ($userModules as $module) {
            // Convert the Module object into an array
            $modules[] = $module->toArray();
        }

        // Setup the return details
        $ret = [
            "id" => $currentAccount->getAccountId(),
            "name" => $currentAccount->getName(),
            "orgName" => $currentAccount->getOrgName(), // TODO: $this->account->get
            "defaultModule" => "home", // TODO: this should be home until it is configurable
            "modules" => $modules
        ];

        $response->write($ret);
        return $response;
    }

    /**
     * Get the account billing details
     *
     * @param HttpRequest $request Request object for this run
     * @return HttpResponse
     */
    public function getGetAccountBillingAction(HttpRequest $request): HttpResponse
    {
        $response = new HttpResponse($request);

        // Make sure that we have an authenticated account
        $currentAccount = $this->getAuthenticatedAccount();
        if (!$currentAccount) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "Account authentication error."]);
            return $response;
        }

        // Get the mainAccountContactId from $account
        $paymentProfileName = null;
        $contactId = null;

        try {
            $contactForAccount = $this->accountBillingService->getContactForAccount($currentAccount);
            $contactId = $contactForAccount->getEntityId();
            $paymentProfileName = $this->accountBillingService->getDefaultPaymentProfileName($currentAccount, $contactId);
        } catch (RuntimeException $ex) {
            $paymentProfileName = "No contact was set for this account: " . $currentAccount->getName();
        }

        // Setup the return details
        $ret = [
            "id" => $currentAccount->getAccountId(),
            "name" => $currentAccount->getName(),
            "status" => $currentAccount->getStatus(),
            "status_name" => $currentAccount->getStatusName(),
            "contact_id" => $contactId,
            "payment_profile_name" => $paymentProfileName,
            "active_users" => $this->accountBillingService->getNumActiveUsers($currentAccount->getAccountId()),
            "per_user" => AccountBillingService::PRICE_PER_USER
        ];

        $response->write($ret);
        return $response;
    }

    /**
     * Update the contact id for the existing account
     *
     * @param HttpRequest $request Request object for this run
     * @return HttpResponse
     */
    public function postUpdateAccountContactAction(HttpRequest $request): HttpResponse
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
        if (!isset($objData['contact_id'])) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "contact_id is a required param."]);
            return $response;
        }

        // Make sure that we have an authenticated account
        $currentAccount = $this->getAuthenticatedAccount();
        if (!$currentAccount) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "Account authentication error."]);
            return $response;
        }

        try {
            $accountData = ["main_account_contact_id" => $objData['contact_id']];
            $result = $this->accountContainer->updateAccount($currentAccount->getAccountId(), $accountData);
            $response->write($result);
            return $response;
        } catch (Exception $ex) {
            $response->setReturnCode(HttpResponse::STATUS_INTERNAL_SERVER_ERROR);
            $response->write(["error" => $ex->getMessage()]);
            return $response;
        }
    }

    /**
     * Update account billing details
     *
     * @param HttpRequest $request Request object for this run
     * @return HttpResponse
     */
    public function postUpdateBillingAction(HttpRequest $request): HttpResponse
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
        if (!isset($objData['contact_id'])) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "contact_id is a required param."]);
            return $response;
        }

        if (!isset($objData['number'])) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "card number is a required param."]);
            return $response;
        }

        if (!isset($objData['ccv'])) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "ccv is a required param."]);
            return $response;
        }

        if (!isset($objData['monthExpires']) || !isset($objData['yearExpires'])) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "expiry date is a required param."]);
            return $response;
        }

        // Make sure that we have an authenticated account
        $currentAccount = $this->getAuthenticatedAccount();
        if (!$currentAccount) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "Account authentication error."]);
            return $response;
        }

        // Create the billing credit card
        $card = new CreditCard();
        $card->setCardNumber($objData['number']);
        $card->setExpiration($objData['yearExpires'], $objData['monthExpires']);
        $card->setCardCode($objData['ccv']);

        try {
            $paymentProfileName = $this->accountBillingService->saveDefaultPaymentProfile($objData['contact_id'], $card);
            $response->write($paymentProfileName);
            return $response;
        } catch (RuntimeException $ex) {
            $response->setReturnCode(HttpResponse::STATUS_INTERNAL_SERVER_ERROR);
            $response->write(["error" => $ex->getMessage()]);
            return $response;
        }
    }
}
