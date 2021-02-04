<?php

namespace Netric\Authentication;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Account\AccountContainerFactory;
use Netric\Request\RequestFactory;
use Netric\Crypt\VaultServiceFactory;

/**
 * Create an authentication service
 */
class AuthenticationServiceFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return AuthenticationService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $accountContainer = $serviceLocator->get(AccountContainerFactory::class);
        $request = $serviceLocator->get(RequestFactory::class);
        $vault = $serviceLocator->get(VaultServiceFactory::class);
        $key = $vault->getSecret("auth_private_key");

        return new AuthenticationService($key, $accountContainer, $request);
    }
}
