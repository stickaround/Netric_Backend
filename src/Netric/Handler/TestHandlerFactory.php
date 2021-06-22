<?php

namespace Netric\Handler;

use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\ServiceManager\ApplicationServiceFactoryInterface;

/**
 * Construct the test hanlder
 */
class TestHandlerFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Construct a controller and return it
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new TestHandler();
    }
}
