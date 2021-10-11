<?php

namespace Netric\Controller;

use Netric\Account\AccountContainerFactory;
use Netric\Authentication\AuthenticationServiceFactory;
use Netric\Mvc\ControllerFactoryInterface;
use Netric\Mvc\ControllerInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Mail\DeliveryServiceFactory;
use Netric\Log\LogFactory;

/**
 * Construct the EmailController for interacting with email messages
 */
class EmailControllerFactory implements ControllerFactoryInterface
{
    /**
     * Construct a controller and return it
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return ControllerInterface
     */
    public function get(ServiceLocatorInterface $serviceLocator): ControllerInterface
    {
        $deliveryService = $serviceLocator->get(DeliveryServiceFactory::class);
        $log = $serviceLocator->get(LogFactory::class);
        $authService = $serviceLocator->get(AuthenticationServiceFactory::class);
        $accountContainer = $serviceLocator->get(AccountContainerFactory::class);
        return new EmailController(
            $deliveryService,
            $log,
            $authService,
            $accountContainer
        );
    }
}
