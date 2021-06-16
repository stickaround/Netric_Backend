<?php
namespace Netric\Application\Health;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Log\LogFactory;
use Netric\Application\Health\DependencyCheck\DependenciesFactory;

/**
 * Construct an application health service
 */
class HealthCheckFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface $serviceLocator For loading dependencies
     * @return HealthCheck
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        $log = $serviceLocator->get(LogFactory::class);
        $dependencies = $serviceLocator->get(DependenciesFactory::class);
        return new HealthCheck($log, $dependencies);
    }
}
