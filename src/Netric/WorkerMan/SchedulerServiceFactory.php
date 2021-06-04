<?php

namespace Netric\WorkerMan;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Aereus\Config\Config;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityQuery\Index\IndexFactory;

/**
 * Handle setting up a job scheduler service
 */
class SchedulerServiceFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface $sl ServiceLocator for injecting dependencies
     * @return SchedulerService
     */
    public function __invoke(ServiceContainerInterface $sl)
    {
        $entityIndex = $sl->get(IndexFactory::class);
        $entityLoader = $sl->get(EntityLoaderFactory::class);

        return new SchedulerService($entityIndex, $entityLoader);
    }
}
