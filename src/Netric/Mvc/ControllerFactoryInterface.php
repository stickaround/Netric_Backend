<?php
namespace Netric\Mvc;

use Aereus\ServiceContainer\ServiceContainerInterface;

/**
 * Controllers must be constructed with a factory to inject any dependencies
 */
interface ControllerFactoryInterface
{
    /**
     * Construct a controller and return it
     *
     * @param ServiceContainerInterface $serviceLocator
     */
    public function get(ServiceContainerInterface $serviceLocator): ControllerInterface;
}
