<?php
namespace Netric\Mvc;

use Netric\ServiceManager\ServiceLocatorInterface;

/**
 * Controllers must be constructed with a factory to inject any dependencies
 */
interface ControllerFactoryInterface
{
    /**
     * Construct a controller and return it
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function get(ServiceLocatorInterface $serviceLocator): ControllerInterface;
}
