<?php

namespace Netric\Controller;

use Netric\Mvc\ControllerFactoryInterface;
use Netric\Mvc\ControllerInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Account\AccountContainerFactory;
use Netric\Authentication\AuthenticationServiceFactory;
use Netric\Account\Module\ModuleServiceFactory;
use Netric\Account\Billing\AccountBillingServiceFactory;
use Netric\PaymentGateway\SystemPaymentGatewayFactory;
use Netric\Entity\EntityLoaderFactory;

/**
 * Construct the AccountController for interacting with email messages
 */
class AccountControllerFactory implements ControllerFactoryInterface
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
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        $moduleService = $serviceLocator->get(ModuleServiceFactory::class);
        $accountBillingService = $serviceLocator->get(AccountBillingServiceFactory::class);

        return new AccountController(
            $accountContainer,
            $authService,
            $entityLoader,
            $moduleService,
            $accountBillingService
        );
    }
}
