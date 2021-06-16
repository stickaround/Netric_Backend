<?php

namespace Netric\Authentication;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Account\AccountContainerFactory;
use Netric\Request\RequestFactory;
use Netric\Crypt\VaultServiceFactory;

/**
 * Create an authentication service
 */
class AuthenticationServiceFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return AuthenticationService
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        $accountContainer = $serviceLocator->get(AccountContainerFactory::class);
        $request = $serviceLocator->get(RequestFactory::class);
        $vault = $serviceLocator->get(VaultServiceFactory::class);
        $key = $vault->getSecret("auth_private_key");

        return new AuthenticationService($key, $accountContainer, $request);
    }
}
