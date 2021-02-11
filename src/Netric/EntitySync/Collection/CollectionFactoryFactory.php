<?php

declare(strict_types=1);

namespace Netric\EntitySync\Collection;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;

/**
 * Create a Entity Collection service
 */
class CollectionFactoryFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Construct an instance of this factory so we can inject it as a dependency
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new CollectionFactory($serviceLocator);
    }
}
