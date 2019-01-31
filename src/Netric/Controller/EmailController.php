<?php
namespace Netric\Controller;

use Netric\Mvc\AbstractAccountController;
use Netric\Application\Response\HttpResponse;

/**
 * Controller for interacting with entities
 */
class EmailController extends AbstractAccountController
{
    /**
     * Get the definition (metadata) of an entity
     */
    public function postSendAction()
    {
        $request = $this->getRequest();
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

        // TODO: Invoke the sender service

        return $response;
    }
}
