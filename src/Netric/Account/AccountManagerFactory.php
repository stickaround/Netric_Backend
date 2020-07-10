<?php

namespace Netric\Account;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;

/**
 * Create database setup service
 */
class AccountManagerFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $sl ServiceLocator for injecting dependencies
     * @return AccountManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new AccountManager();
    }
}
