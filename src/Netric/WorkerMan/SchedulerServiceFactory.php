<?php
namespace Netric\WorkerMan;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Config\Config;

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
        $entityIndex = $sl->get('Netric/EntityQuery/Index/Index');
        $entityLoader = $sl->get("EntityLoader");

        return new SchedulerService($entityIndex, $entityLoader);
    }
}
