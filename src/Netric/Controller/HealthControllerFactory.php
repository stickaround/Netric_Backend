<?php

namespace Netric\Controller;

use Netric\Mvc\ControllerFactoryInterface;
use Netric\Mvc\ControllerInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Application\Health\HealthCheckFactory;
use Netric\Log\LogFactory;

/**
 * Construct the FilesControllerFactory for interacting with email messages
 */
class HealthControllerFactory implements ControllerFactoryInterface
{
    /**
     * Construct a controller and return it
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return ControllerInterface
     */
    public function get(ServiceLocatorInterface $serviceLocator): ControllerInterface
    {
        $healthCheck = $serviceLocator->get(HealthCheckFactory::class);
        $log = $serviceLocator->get(LogFactory::class);

        return new HealthController(
            $healthCheck,
            $log
        );
    }
}
