<?php

namespace Netric\Handler;

use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Account\AccountContainerFactory;
use Netric\Authentication\AuthenticationServiceFactory;
use Netric\ServiceManager\ApplicationServiceFactoryInterface;

/**
 * Construct the AuthenticationControllerFactory for interacting with email messages
 */
class AuthenticationHandlerFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Construct a controller and return it
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return AuthenticationHandler
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $accountContainer = $serviceLocator->get(AccountContainerFactory::class);
        $authService = $serviceLocator->get(AuthenticationServiceFactory::class);
        $application = $serviceLocator->getApplication();

        return new AuthenticationHandler(
            $accountContainer,
            $authService,
            $application
        );
    }
}
