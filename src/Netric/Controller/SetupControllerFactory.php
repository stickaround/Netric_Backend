<?php

namespace Netric\Controller;

use Netric\Mvc\ControllerFactoryInterface;
use Netric\Mvc\ControllerInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Account\AccountContainerFactory;
use Netric\Authentication\AuthenticationServiceFactory;
use Netric\Account\AccountSetupFactory;
use Netric\Application\DatabaseSetupFactory;
use Netric\Application\Setup\AccountUpdaterFactory;
use Netric\Log\LogFactory;

/**
 * Construct the SetupControllerFactory for interacting with email messages
 */
class SetupControllerFactory implements ControllerFactoryInterface
{
    /**
     * Construct a controller and return it
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return ControllerInterface
     */
    public function get(ServiceLocatorInterface $serviceLocator): ControllerInterface
    {
        $accountContainer = $serviceLocator->get(AccountContainerFactory::class);
        $authService = $serviceLocator->get(AuthenticationServiceFactory::class);
        $accountSetup = $serviceLocator->get(AccountSetupFactory::class);
        $dbSetup = $serviceLocator->get(DatabaseSetupFactory::class);
        $accountUpdater = $serviceLocator->get(AccountUpdaterFactory::class);
        $log = $serviceLocator->get(LogFactory::class);
        $application = $serviceLocator->getApplication();

        return new SetupController(
            $accountContainer,
            $authService,
            $accountSetup,
            $dbSetup,
            $accountUpdater,
            $log,
            $application
        );
    }
}
