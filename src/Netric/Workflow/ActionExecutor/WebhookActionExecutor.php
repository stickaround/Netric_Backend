<?php

declare(strict_types=1);

namespace Netric\Workflow\ActionExecutor;

use Netric\Entity\EntityInterface;
use Netric\Entity\ObjType\UserEntity;

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

        // TODO: call the url and return true if the status code is 200
        // but for now, we just return false for failure to stop execution
        return false;
    }
}
