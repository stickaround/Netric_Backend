<?php

declare(strict_types=1);

namespace Netric\Controller;

use Netric\Account\Account;
use Netric\Account\AccountContainerInterface;
use Netric\Log\LogInterface;
use Netric\Mvc\ControllerInterface;
use Netric\Application\Response\HttpResponse;
use Netric\Authentication\AuthenticationService;
use Netric\Request\HttpRequest;
use Netric\Entity\EntityLoader;
use Netric\Mail\SenderService;
use Netric\Mail\DeliveryService;
use Netric\Mvc\AbstractFactoriedController;
use RuntimeException;

/**
 * Create controller to test sending email
 */
class TestmailController extends AbstractFactoriedController implements ControllerInterface
{
    
    /**
     * Sender service to interact with SMTP transport
     */
    private SenderService $senderService;

    /**
     * Delivery service saves imported messages
     */
    private DeliveryService $deliveryService;


    /**
     * Container used to load accounts
     */
    private AccountContainerInterface $accountContainer;

    /**
     * Service used to get the current user/account
     */
    private AuthenticationService $authService;

    /**
     * If in test mode, we don't do file upload validation
     *
     * @var bool
     */
    public $testMode = false;

    /**
     * Initialize controller and all dependencies
     *
     * @param SenderService $senderService
     * @param DeliveryService $deliveryService
     */
    public function __construct(
        EntityLoader $entityLoader,
        SenderService $senderService,
        DeliveryService $deliveryService,
        AuthenticationService $authService,
        AccountContainerInterface $accountContainer
    ) {
        $this->entityLoader = $entityLoader;
        $this->senderService = $senderService;
        $this->deliveryService = $deliveryService;
        $this->authService = $authService;
        $this->accountContainer = $accountContainer;
    }

    /**
     * Send an email
     *
     * @param HttpRequest $request Request object for this run
     * @return HttpResponse
     */
    public function postSendAction(HttpRequest $request): HttpResponse
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

        // At the very least we required that the id of a saved message be set to send it
        if (!isset($objData['entity_id'])) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write("entity_id is a required param");
            return $response;
        }

        // Make sure that we have an authenticated account
        $currentAccount = $this->getAuthenticatedAccount();
        if (!$currentAccount) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(['error' => "No authenticated account found."]);
            return $response;
        }

        // Get the email entity to send
        $emailMessage = $this->entityLoader->getEntityById(
            $objData['entity_id'],
            $currentAccount->getAccountId()
        );

        // Return 404 if message was not found to send
        if ($emailMessage === null) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_NOT_FOUND);
            $response->write("No message id {$objData['entity_id']} was found");
            return $response;
        }

        // Send the message with the sender service
        $sentStatus = $this->senderService->send($emailMessage);
        $response->write(['result' => $sentStatus]);
        return $response;
    }    

    /**
     * Get the currently authenticated account
     *
     * @return Account
     */
    private function getAuthenticatedAccount(): Account
    {
        $authIdentity = $this->authService->getIdentity();
        if (!$authIdentity) {
            return null;
        }

        return $this->accountContainer->loadById($authIdentity->getAccountId());
    }
}
