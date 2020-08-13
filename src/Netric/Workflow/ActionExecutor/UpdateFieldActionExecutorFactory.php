<?php

declare(strict_types=1);

namespace Netric\Workflow\ActionExecutor;

use Netric\Config\ConfigFactory;
use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\Entity\EntityLoaderFactory;
use Netric\Entity\ObjType\WorkflowActionEntity;

/**
 * Factory to create a new UpdateFieldActionExecutor
 */
class UpdateFieldActionExecutorFactory
{
    /**
     * Construct action exector with dependencies
     *
     * @param AccountServiceManagerInterface $serviceLocator For loading dependencies
     * @return ActionExectorInterface
     */
    public static function create(
        AccountServiceManagerInterface $serviceLocator,
        WorkflowActionEntity $actionEntity
    ): ActionExecutorInterface {
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        $config = $serviceLocator->get(ConfigFactory::class);
        return new UpdateFieldActionExecutor($entityLoader, $actionEntity, $config->application_url);
    }
}
