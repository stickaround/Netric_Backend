<?php

declare(strict_types=1);


namespace Netric\Workflow\ActionExecutor;

use Error;
use Netric\Entity\EntityInterface;
use Netric\Entity\EntityLoader;
use Netric\Entity\ObjType\UserEntity;
use Netric\Entity\ObjType\WorkflowActionEntity;
use Netric\Workflow\WorkflowService;

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
     * Service for starting workflwos
     */
    private WorkflowService $workflowService;

    /**
     * Constructor
     *
     * @param EntityLoader $entityLoader
     * @param WorkflowActionEntity $actionEntity
     * @param string $appliactionUrl
     */
    public function __construct(
        EntityLoader $entityLoader,
        WorkflowActionEntity $actionEntity,
        string $applicationUrl,
        WorkflowService $workflowService
    ) {
        $this->workflowService = $workflowService;

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
        $entityActive = $this->getParam('f_active', $actOnEntity);


        // TODO: call the url and return true if the status code is 200
        // but for now, we just return false for failure to stop execution
        if (!empty($url) && $entityActive) {
            // Start the workflow for the entity
            $workflow = $this->getEntityloader()->getEntityById($url, $user->getAccountId());
            $this->workflowService->startInstanceAndRunActions($workflow, $actOnEntity, $user);
            return true;
        }

        if (!$url) {
            $this->addError(new Error("Check your url. You need to set url."));
            return false;
        }
       
        return false;
    }
}
