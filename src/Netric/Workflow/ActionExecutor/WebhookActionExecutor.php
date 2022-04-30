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
        $entityActive = $this->getParam('f_active', $actOnEntity);


        // TODO: call the url and return true if the status code is 200
        // but for now, we just return false for failure to stop execution
        if (!empty($url) && $entityActive) {
            // Start the workflow for the entity
            $workflow = $this->getEntityloader()->getEntityById($url, $user->getAccountId());
            //$get = file_get_contents($url);
            // create a new cURL resource
            $ch = curl_init();

            // set URL and other appropriate options
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);

            // grab URL and pass it to the browser
            curl_exec($ch);

            // close cURL resource, and free up system resources
            curl_close($ch);

            return true;
        }

        if (!$url) {
            $this->addError(new Error("Check your url. You need to set url."));
            return false;
        }
       
        return false;
    }
}
