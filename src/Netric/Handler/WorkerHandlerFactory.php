<?php

declare(strict_types=1);

namespace Netric\Handler;

use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\WorkerMan\WorkerServiceFactory;

/**
 * Construct the worker hanlder
 */
class WorkerHandlerFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Construct handler and return it
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $workerService = $serviceLocator->get(WorkerServiceFactory::class);
        return new WorkerHandler($workerService);
    }
}
