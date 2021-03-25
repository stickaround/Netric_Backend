<?php

namespace Netric\Account;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Cache\CacheFactory;
use Netric\Application\DataMapperFactory as ApplicationDataMapperFactory;

/**
 * Create a new Application DataMapper service
 */
class AccountContainerFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return AccountContainerInterface
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
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
