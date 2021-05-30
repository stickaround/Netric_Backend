<?php
namespace Netric\Controller;

use Netric\Mvc\ControllerFactoryInterface;
use Netric\Mvc\ControllerInterface;

// This will now remove and replace with Aereus ServiceContainer
// use Netric\ServiceManager\ServiceLocatorInterface;    

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;

/**
 * Construct test controller and implements Aereus ServiceContainer FactoryInterface.
 * This is to replace the current Netric\ServiceManager ServiceLocator
 */
class TestControllerFactory implements FactoryInterface
{
    /**
     * Construct a controller and return a ControllerInterface value type
     *
     * @param ServiceContainerInterface $container
     * @return ControllerInterface
     */
    public function __invoke(ServiceContainerInterface $container): ControllerInterface
    {
        return new TestController();
    }
}
