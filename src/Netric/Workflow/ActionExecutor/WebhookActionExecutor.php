<?php

declare(strict_types=1);


namespace Netric\Workflow\ActionExecutor;

use Netric\Entity\EntityInterface;
use Netric\Entity\EntityLoader;
use Netric\Entity\ObjType\UserEntity;
use Netric\Entity\ObjType\WorkflowActionEntity;
use Netric\Workflow\WorkflowService;
use Netric\Error\Error;

/**
 * Action to call an external page - very useful for API integration
 *
 * Params in the 'data' field:
 *
 *  url string REQUIRED the URL to call when the action is executed
 */
class WebhookActionExecutor extends AbstractActionExecutor implements ActionExecutorInterface
{

    /**
     * Execute action on an entity
     *
     * @param EntityInterface $actOnEntity The entity (any type) we are acting on
     * @param UserEntity $user The user who is initiating the action
     * @return bool true on success, false on failure
     */
    public function execute(EntityInterface $actOnEntity, UserEntity $user): bool
    {
        // Get url from the param
        $url = $this->getParam('url', $actOnEntity);

        // if not url exist then return false
        if (!$url) {
            $this->addError(new Error("Check your url. You need to set url."));
            return false;
        }

        //default curl error message
        $error_msg = 'Network Error';

        // TODO: call the url and return true if the status code is 200
        // create a new cURL resource
        $ch = curl_init($url);
        // set URL and other appropriate options
        curl_setopt($ch, CURLOPT_HEADER, true);    // we want headers
        curl_setopt($ch, CURLOPT_NOBODY, true);    // we don't need body
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_TIMEOUT,10);
        // grab URL and pass it to the browser
        curl_exec($ch);
        //Get status code
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        //Get Error message, if found
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
        }

        // close cURL resource, and free up system resources
        curl_close($ch);

        //Check Status code
        if($httpcode === 200){
            return true;
        }
        // Return Error message
        $this->addError(new Error($error_msg));
        return false;
    }
}
