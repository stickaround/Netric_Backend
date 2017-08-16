<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\WorkerMan;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;

/**
 * Handle setting up a worker service
 */
class WorkerServiceFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $sl ServiceLocator for injecting dependencies
     * @return WorkerService
     */
    public function createService(ServiceLocatorInterface $sl)
    {
        // Get the application data mapper since it implements the SchedulerDataMapperInterface
        $dataMapper = $sl->get('Netric/WorkerMan/Scheduler/SchedulerDataMapper');
        return new SchedulerService($dataMapper);
    }
}
