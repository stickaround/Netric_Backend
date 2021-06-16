<?php

namespace Netric\Entity;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;

/**
 * Create a new EntityFactory service
 */
class EntityFactoryFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return EntityFactory
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new EntityFactory($serviceLocator);
    }
}
