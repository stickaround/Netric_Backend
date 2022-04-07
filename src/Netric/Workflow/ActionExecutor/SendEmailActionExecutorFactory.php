<?php

namespace Netric\Workflow\ActionExecutor;

use Netric\Config\ConfigFactory;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Entity\EntityLoaderFactory;
use Netric\Entity\ObjType\WorkflowActionEntity;
use Netric\Mail\SenderServiceFactory;

/**
 * Factory to create a new action executor
 */
class SendEmailActionExecutorFactory
{
    /**
     * Construct action executor with dependencies
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
        $senderService = $serviceLocator->get(SenderServiceFactory::class);
        $config = $serviceLocator->get(ConfigFactory::class);

        return new SendEmailActionExecutor(
            $entityLoader,
            $actionEntity,
            $config->application_url,
            $senderService
        );
    }
}
