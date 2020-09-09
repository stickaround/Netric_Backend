<?php

declare(strict_types=1);

namespace Netric\EntitySync\Collection;

use Netric\ServiceManager\AccountServiceFactoryInterface;
use Netric\ServiceManager\AccountServiceManagerInterface;

/**
 * Create a Entity Collection service
 */
class CollectionFactoryFactory implements AccountServiceFactoryInterface
{
    /**
     * Construct an instance of this factory so we can inject it as a dependency
     *
     * @param AccountServiceManagerInterface $serviceLocator ServiceLocator for injecting dependencies
     */
    public function createService(AccountServiceManagerInterface $serviceLocator)
    {
        return new CollectionFactory($serviceLocator);
    }
}