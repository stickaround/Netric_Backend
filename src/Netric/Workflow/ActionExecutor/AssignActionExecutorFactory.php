<?php

namespace Netric\Workflow\ActionExecutor;

use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\Entity\ObjType\WorkflowActionEntity;

/**
 * Factory to create a new AssignActionExecutor
 */
class AssignActionExecutorFactory
{
    /**
     * Construct action exector with dependencies
     *
     * @param ServiceLocatorInterface $serviceLocator For loading dependencies
     * @return ActionExectorInterface
     */
    public static function create(
        ServiceLocatorInterface $serviceLocator,
        WorkflowActionEntity $actionEntity
    ): ActionExecutorInterface {

        // Setup dependencies
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        $config = $serviceLocator->get(ConfigFactory::class);
        $entityIndex = $serviceLocator->get(IndexFactory::class);

        return new AssignActionExecutor(
            $entityLoader,
            $actionEntity,
            $config->application_url,
            $entityIndex
        );
    }
}
