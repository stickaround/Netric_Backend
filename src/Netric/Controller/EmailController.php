<?php
namespace Netric\Controller;

use Netric\Mvc\ControllerInterface;
use Netric\Application\Response\HttpResponse;
use Netric\Entity\EntityLoader;
use Netric\Mail\SenderService;
use Netric\Request\HttpRequest;

/**
 * Controller for interacting with entities
 */
class EmailController implements ControllerInterface
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
     * Get the definition (metadata) of an entity
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

        if (!isset($objData['guid'])) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write("guid is a required param");
            return $response;
        }

        // Get the email entity to send
        $emailMessage = $this->entityLoader->getByGuid($objData['guid']);

        // Send the message with the sender service
        $sentStatus = $this->senderService->send($emailMessage);
        $response->write(['sent' => $sentStatus]);
        return $response;
    }
}
