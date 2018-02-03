<?php
namespace Netric\WorkerMan;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Config\Config;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityQuery\Index\IndexFactory;

/**
 * Handle setting up a job scheduler service
 */
class SchedulerServiceFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $sl ServiceLocator for injecting dependencies
     * @return SchedulerService
     */
    public function createService(ServiceLocatorInterface $sl)
    {
        $entityIndex = $sl->get(IndexFactory::class);
        $entityLoader = $sl->get(EntityLoaderFactory::class);

        return new SchedulerService($entityIndex, $entityLoader);
    }
}
