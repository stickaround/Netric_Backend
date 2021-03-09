<?php

namespace Netric\Controller;

use Netric\Entity\Notifier\Notifier;
use Netric\Mvc\ControllerInterface;
use Netric\Mvc\AbstractFactoriedController;
use Netric\Application\Response\HttpResponse;
use Netric\Request\HttpRequest;

/**
 * Controller for interacting with notifications
 */
class NotificationController extends AbstractFactoriedController implements ControllerInterface
{
    /**
     * Notification pusher client
     */
    private Notifier $notifier;

    /**
     * Initialize controller and all dependencies
     *
     * @param Notifier $notifier Service for managing notifications
     */
    public function __construct(Notifier $notifier)
    {
        $this->notifier = $notifier;
    }
    /**
     * Setup a subscription to a push notification channel
     *
     * @param HttpRequest $request
     * @return HttpResponse
     */
    public function postSubscribeAction(HttpRequest $request): HttpResponse
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
        if (!isset($objData['channel']) || !isset($objData['user_id']) || !isset($objData['channel_data'])) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write("channel, user_id, and channel_data are all required params");
            return $response;
        }

        $res = $this->notifier->subscribeToPush($objData['user_id'], $objData['channel'], $objData['channel_data']);

        // Send the message with the sender service
        $response->write(['result' => ($res) ? 'SUCCESS' : 'FAIL']);
        return $response;
    }
}
