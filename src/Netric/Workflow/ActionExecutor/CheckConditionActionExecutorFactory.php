<?php

declare(strict_types=1);

namespace Netric\Workflow\ActionExecutor;

use Netric\Config\ConfigFactory;
use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\Entity\EntityLoaderFactory;
use Netric\Entity\ObjType\WorkflowActionEntity;
use Netric\EntityQuery\Index\IndexFactory;

/**
 * Factory to create a new UpdateFieldActionExecutor
 */
class CheckConditionActionExecutorFactory
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

        // Setup dependencies
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        $entityIndex = $serviceLocator->get(IndexFactory::class);
        $config = $serviceLocator->get(ConfigFactory::class);

        // Retrun new executor
        return new CheckConditionActionExecutor(
            $entityLoader,
            $actionEntity,
            $config->application_url,
            $entityIndex
        );
    }
}
