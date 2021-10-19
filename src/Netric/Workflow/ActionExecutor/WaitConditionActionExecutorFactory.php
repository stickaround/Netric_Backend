<?php

declare(strict_types=1);

namespace Netric\Workflow\ActionExecutor;

use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Entity\EntityLoaderFactory;
use Netric\Entity\ObjType\WorkflowActionEntity;
use Netric\WorkerMan\SchedulerServiceFactory;
use Netric\Config\ConfigFactory;

/**
 * Create a new WaitConditionAction
 */
class WaitConditionActionExecutorFactory
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
        $jobScheduler = $serviceLocator->get(SchedulerServiceFactory::class);

        return new WaitConditionActionExecutor(
            $entityLoader,
            $actionEntity,
            $config->application_url,
            $jobScheduler
        );
    }
}
