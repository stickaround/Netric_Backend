<?php

declare(strict_types=1);

namespace Netric\EntitySync\Collection;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;

/**
 * Create a Entity Collection service
 */
class CollectionFactoryFactory implements FactoryInterface
{
    /**
     * Construct an instance of this factory so we can inject it as a dependency
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        return new CollectionFactory($serviceLocator);
    }
}
