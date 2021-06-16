<?php
/*
 * Demo factory used for testing
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\ServiceManager\Test;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;

/**
 * Class used to demonstrate loading a service through the ServiceManager
 */
class ServiceFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return mixed Initailized service object
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new Service();
    }
}
