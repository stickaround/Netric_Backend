<?php

namespace Netric\Controller;

use Netric\Mvc\ControllerFactoryInterface;
use Netric\Mvc\ControllerInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Account\AccountContainerFactory;
use Netric\Authentication\AuthenticationServiceFactory;
use Netric\Account\AccountSetupFactory;
use Netric\Log\LogFactory;

/**
 * Construct the SetupControllerFactory for interacting with email messages
 */
class SetupControllerFactory implements ControllerFactoryInterface
{
    /**
     * Construct a controller and return it
     *
     * @param ServiceContainerInterface $serviceLocator
     * @return ControllerInterface
     */
    public function get(ServiceContainerInterface $serviceLocator): ControllerInterface
    {
        $accountContainer = $serviceLocator->get(AccountContainerFactory::class);
        $authService = $serviceLocator->get(AuthenticationServiceFactory::class);
        $accountSetup = $serviceLocator->get(AccountSetupFactory::class);
        $log = $serviceLocator->get(LogFactory::class);
        $application = $serviceLocator->getApplication();

        return new SetupController(
            $accountContainer,
            $authService,
            $accountSetup,
            $log,
            $application
        );
    }
}
