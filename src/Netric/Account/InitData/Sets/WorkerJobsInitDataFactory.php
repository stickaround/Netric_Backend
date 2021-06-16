<?php

declare(strict_types=1);

namespace Netric\Account\InitData\Sets;

use Netric\Entity\EntityLoaderFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\WorkerMan\SchedulerServiceFactory;

/**
 * Return data intializer
 */
class WorkerJobsInitDataFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return InitDataInterface[]
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        $data = require(__DIR__ . '/../../../../../data/account/worker-jobs.php');
        $entityIndex = $serviceLocator->get(IndexFactory::class);
        $schedulerService = $serviceLocator->get(SchedulerServiceFactory::class);
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        return new WorkerJobsInitData($data, $entityIndex, $schedulerService, $entityLoader);
    }
}
