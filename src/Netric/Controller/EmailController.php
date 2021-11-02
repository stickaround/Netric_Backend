<?php

namespace Netric\Controller;

use Netric\Account\Account;
use Netric\Account\AccountContainerInterface;
use Netric\Log\LogInterface;
use Netric\Mvc\ControllerInterface;
use Netric\Application\Response\HttpResponse;
use Netric\Request\HttpRequest;
use Netric\Mail\DeliveryService;
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
     * If in test mode, we don't do file upload validation
     *
     * @var bool
     */
    public $testMode = false;

    /**
     * Initialize controller and all dependencies
     *
     * @param DeliveryService $deliveryService
     * @param LogInterface $log
     */
    public function __construct(
        DeliveryService $deliveryService,
        LogInterface $log,
        AccountContainerInterface $accountContainer
    ) {
        $this->deliveryService = $deliveryService;
        $this->log = $log;
        $this->accountContainer = $accountContainer;
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
            $response->write(['error' => "RAW message missing or failed to upload"]);
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
}
