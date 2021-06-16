<?php

namespace Netric\Entity;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;

/**
 * Create a new EntityFactory service
 */
class EntityFactoryFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return EntityFactory
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        return new EntityFactory($serviceLocator);
    }
}
