<?php

declare(strict_types=1);

namespace Netric\Workflow\ActionExecutor;

use Netric\Entity\EntityInterface;
use Netric\Entity\EntityLoader;
use Netric\Entity\ObjType\UserEntity;
use Netric\Entity\ObjType\WorkflowActionEntity;
use Netric\Workflow\WorkflowService;
use Netric\Error\Error;
use Netric\Curl\HttpCaller;

/**
 * Action to call an external page - very useful for API integration
 *
 * Params in the 'data' field:
 *
 *  url string REQUIRED the URL to call when the action is executed
 */
class WebhookActionExecutor extends AbstractActionExecutor implements ActionExecutorInterface
{
    /*
     * HttpCaller services to get cUrl transfer responses
    */
    private HttpCaller $httpCaller;

    /*
    *  Store error message
    *  Initize with default value
    */
    private $errorMessage = 'Network Error';

    /**
     * Constructor
     *
     * @param EntityLoader $entityLoader
     * @param WorkflowActionEntity $actionEntity
     * @param string $appliactionUrl
     * @param HttpCaller $httpCaller;
     */
    public function __construct(EntityLoader $entityLoader,
        WorkflowActionEntity $actionEntity,
        string $applicationUrl,
        HttpCaller $httpCaller) {
        $this->httpCaller = $httpCaller;

        // Should always call the parent constructor for base dependencies
        parent::__construct($entityLoader, $actionEntity, $applicationUrl);
    }

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
       
        // Call httpcaller GET method
        $this->httpCaller->get($url);
        
        // Get status code for Get method
        $httpcode = $this->httpCaller->getInfo(CURLINFO_HTTP_CODE);

        // Check Error message, if found
        if ($this->httpCaller->getError()) {
            $this->errorMessage = $this->httpCaller->getErrorMessage();
        }

        // close cURL resource, and free up system resources
        $this->httpCaller->close();

        // return true if the status code is 200
        if($httpcode === 200){
            return true;
        }
        // Return false and add Error
        $this->addError(new Error($this->errorMessage));
        return false;
    }

}
