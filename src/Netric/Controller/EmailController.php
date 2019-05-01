<?php
namespace Netric\Controller;

use Netric\Mvc\ControllerInterface;
use Netric\Application\Response\HttpResponse;
use Netric\Request\HttpRequest;
use Netric\Entity\EntityLoader;
use Netric\Mail\SenderService;
use Netric\Mvc\AbstractFactoriedController;

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
     * Initialize controller and all dependencies
     *
     * @param EntityLoader $entityLoader
     * @param SenderService $senderService
     */
    public function __construct(EntityLoader $entityLoader, SenderService $senderService)
    {
        $this->entityLoader = $entityLoader;
        $this->senderService = $senderService;
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
        if (!isset($objData['guid'])) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write("guid is a required param");
            return $response;
        }

        // Get the email entity to send
        $emailMessage = $this->entityLoader->getByGuid($objData['guid']);

        // Return 404 if message was not found to send
        if ($emailMessage === null) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_NOT_FOUND);
            $response->write("No message guid {$objData['guid']} was found");
            return $response;
        }

        // Send the message with the sender service
        $sentStatus = $this->senderService->send($emailMessage);
        $response->write(['result' => $sentStatus]);
        return $response;
    }

    /**
     * Deliver a new email message
     *
     * @param HttpRequest $request Request object for this run
     * @return HttpResponse
     */
    public function postReceiveAction(HttpRequest $request): HttpResponse
    {
        $response = new HttpResponse($request);

        $uploadedMessageFile = $request->getParam('message');

        if (!is_uploaded_file($uploadedMessageFile['tmp_name'])) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write("RAW message missing or failed to upload");
            return $response;
        }


        // TODO: Stream inbound file
        // $rawBody = $request->getBody();

        return $response;
    }
}
