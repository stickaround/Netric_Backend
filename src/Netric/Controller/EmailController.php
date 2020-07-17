<?php

namespace Netric\Controller;

use Netric\Log\LogInterface;
use Netric\Mvc\ControllerInterface;
use Netric\Application\Response\HttpResponse;
use Netric\Request\HttpRequest;
use Netric\Entity\EntityLoader;
use Netric\Mail\SenderService;
use Netric\Mail\DeliveryService;
use Netric\Mvc\AbstractFactoriedController;
use RuntimeException;

/**
 * Controller for interacting with entities
 */
class EmailController extends AbstractFactoriedController implements ControllerInterface
{
    /**
     * Entity loader to get messages
     *
     * @var EntityLoader
     */
    private $entityLoader;

    /**
     * Sender service to interact with SMTP transport
     *
     * @var SenderService
     */
    private $senderService;

    /**
     * Delivery service saves imported messages
     *
     * @var DeliveryService
     */
    private $deliveryService;

    /**
     * @var LogInterface
     */
    private $log;

    /**
     * If in test mode, we don't do file upload validation
     *
     * @var bool
     */
    public $testMode = false;

    /**
     * Initialize controller and all dependencies
     *
     * @param EntityLoader $entityLoader
     * @param SenderService $senderService
     * @param DeliveryService $deliveryService
     * @param LogInterface $log
     */
    public function __construct(
        EntityLoader $entityLoader,
        SenderService $senderService,
        DeliveryService $deliveryService,
        LogInterface $log
    ) {
        $this->entityLoader = $entityLoader;
        $this->senderService = $senderService;
        $this->deliveryService = $deliveryService;
        $this->log = $log;
    }

    /**
     * Send an email that was previously saved as an email_message entity
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

        // At the very least we required that the guid of a saved message be set to send it
        if (!isset($objData['entity_id'])) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write("entity_id is a required param");
            return $response;
        }

        // Get the email entity to send
        $emailMessage = $this->entityLoader->getByGuid($objData['entity_id']);

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
        $recipient = $request->getParam('recipient');

        if (!$recipient) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(['error' => "'recipient' is a required param"]);
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
            $messageGuid = $this->deliveryService->deliverMessageFromFile($recipient, $files['message']['tmp_name']);
            $response->setReturnCode(HttpResponse::STATUS_CODE_OK);
            $response->write(['result' => true, 'entity_id' => $messageGuid]);
            return $response;
        } catch (RuntimeException $exception) {
            $this->log->error(
                "EmailController::postReceiveAction: failed to deliver message to $recipient - " .
                    $exception->getMessage()
            );
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(['error' => $exception->getMessage()]);
            return $response;
        }
    }
}
