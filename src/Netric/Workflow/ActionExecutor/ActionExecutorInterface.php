<?php

declare(strict_types=1);

namespace Netric\Workflow\ActionExecutor;

use Netric\Entity\EntityInterface;
use Netric\Error\ErrorAwareInterface;
use Netric\Entity\ObjType\WorkflowActionEntity;
use Netric\Entity\ObjType\UserEntity;

/**
 * Common interface for all workflow action executors
 */
interface ActionExecutorInterface extends ErrorAwareInterface
{
    /**
     * Execute an action on an entity
     *
     * @param EntityInterface $actOnEntity The entity (any type) we are acting on
     * @param UserEntity $user The user who is initiating the action
     * @return bool true on success, false on failure
     */
    public function execute(EntityInterface $actOnEntity, UserEntity $user): bool;
}
