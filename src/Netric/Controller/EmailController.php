<?php

namespace Netric\Controller;

use Netric\Account\Account;
use Netric\Account\AccountContainerInterface;
use Netric\Authentication\AuthenticationService;
use Netric\Log\LogInterface;
use Netric\Mvc\ControllerInterface;
use Netric\Application\Response\HttpResponse;
use Netric\Request\HttpRequest;
use Netric\Mail\DeliveryService;
use Netric\Mail\MailSystem;
use Netric\Mvc\AbstractFactoriedController;
use RuntimeException;

/**
 * Controller for interacting with entities
 */
class EmailController extends AbstractFactoriedController implements ControllerInterface
{
    /**
     * Delivery service saves imported messages
     */
    private DeliveryService $deliveryService;

    /**
     * Application log
     */
    private LogInterface $log;

    /**
     * Service used to get the current user/account
     */
    private AuthenticationService $authService;

    /**
     * Interact with the global netric mailsystem and will be used to get the email domains
     */
    private MailSystem $mailSystem;

    /**
     * Initialize controller and all dependencies
     *
     * @param DeliveryService $deliveryService
     * @param LogInterface $log
     */
    public function __construct(
        DeliveryService $deliveryService,
        LogInterface $log,
        AccountContainerInterface $accountContainer,
        AuthenticationService $authService,
        MailSystem $mailSystem,
    ) {
        $this->deliveryService = $deliveryService;
        $this->log = $log;
        $this->accountContainer = $accountContainer;
        $this->mailSystem = $mailSystem;
        $this->authService = $authService;
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
     * Deliver a email message
     *
     * This is normally called from the SMTP server to deliver a message to
     * a known recipient. However, it is entirely possible to call it directly
     * for delivery if bypassing an SMTP gateway is needed.
     *
     * @param HttpRequest $request Request object for this run
     * @return HttpResponse
     */
    public function postReceiveAction(HttpRequest $request): HttpResponse
    {
        $response = new HttpResponse($request);

        // Messages are sent as a multipart form with a file param called 'message'
        $files = $request->getParam('files');

        // In netric, all email is routed to incoming@[accountname].[domain.com]
        // The recipient is this incoming mailbox, but the 'to' is the actual
        // recipient in netric and we'll use it to deliver mail to the right
        // email_acount from netric.
        $incomingMailboxAddress = $request->getParam('recipient');
        $to = $request->getParam('to');

        $this->log->info("EmailController->postReceiveAction: Delivering for: $incomingMailboxAddress, $to");

        if (!$incomingMailboxAddress || !$to) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(['error' => "'recipient and to are required params"]);
            return $response;
        }

        if (!isset($files['message']) || !is_array($files['message'])) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(['error' => "'message' is a required param"]);
            return $response;
        }

        // Make sure the file was uploaded by PHP (or we're in a unit test with testMode)
        if (!is_uploaded_file($files['message']['tmp_name']) && !$this->testMode) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write([
                'error' => "RAW message missing or failed to upload: " .
                    var_export($files['message'], true)
            ]);
            return $response;
        }

        // Try to import message
        try {
            $messageGuid = $this->deliveryService->deliverMessageFromFile(
                $to,
                $files['message']['tmp_name'],
            );
            $response->setReturnCode(HttpResponse::STATUS_CODE_OK);
            $response->write(['result' => true, 'entity_id' => $messageGuid]);
            return $response;
        } catch (RuntimeException $exception) {
            $this->log->error(
                "EmailController::postReceiveAction: failed to deliver message to $incomingMailboxAddress - " .
                    $exception->getMessage()
            );
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(['error' => $exception->getMessage()]);
            return $response;
        }
    }

    /**
     * Get all domains for an account
     *
     * @param HttpRequest $request Request object for this run
     * @return HttpResponse
     */
    public function getGetDomainsByAccountAction(HttpRequest $request): HttpResponse
    {
        $response = new HttpResponse($request);

        try {
            $currentAccount = $this->getAuthenticatedAccount();
            if (!$currentAccount) {
                $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
                $response->write(["error" => "Account authentication error."]);
                return $response;
            }

            $accountId = $currentAccount->getAccountId();
            if (!$accountId) {
                $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
                $response->write(["error" => "Account authentication error."]);
                return $response;
            }

            // Get all domains for the authenticated account
            $domains = $this->mailSystem->getDomainsByAccount($accountId);

            if (!$domains) {
                $response->setReturnCode(HttpResponse::STATUS_INTERNAL_SERVER_ERROR);
                $response->write(["error" => "Error wile trying to get all the domains for account: $accountId"]);
                return $response;
            }

            $response->write($domains);
            return $response;
        } catch (Exception $ex) {
            $response->setReturnCode(HttpResponse::STATUS_INTERNAL_SERVER_ERROR);
            $response->write(["error" => $ex->getMessage()]);
        }
    }
}
