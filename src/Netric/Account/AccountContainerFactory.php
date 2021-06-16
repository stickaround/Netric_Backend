<?php

namespace Netric\Account;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Cache\CacheFactory;
use Netric\Application\DataMapperFactory as ApplicationDataMapperFactory;

/**
 * Create a new Application DataMapper service
 */
class AccountContainerFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return AccountContainerInterface
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        $cache = $serviceLocator->get(CacheFactory::class);
        $appDataMapper = $serviceLocator->get(ApplicationDataMapperFactory::class);
        return new AccountContainer(
            $appDataMapper,
            $cache,
            $serviceLocator->getApplication()
        );
    }
}
