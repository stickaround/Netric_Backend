<?php

namespace Netric\Controller;

use Netric\Account\AccountContainerFactory;
use Netric\Authentication\AuthenticationServiceFactory;
use Netric\Mvc\ControllerFactoryInterface;
use Netric\Mvc\ControllerInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Mail\SenderServiceFactory;
use Netric\Mail\DeliveryServiceFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\Log\LogFactory;

/**
 * Construct the EmailController for interacting with email messages
 */
class EmailControllerFactory implements ControllerFactoryInterface
{
    /**
     * Construct a controller and return it
     *
     * @param ServiceContainerInterface $serviceLocator
     * @return ControllerInterface
     */
    public function get(ServiceContainerInterface $serviceLocator): ControllerInterface
    {
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        $senderService = $serviceLocator->get(SenderServiceFactory::class);
        $deliveryService = $serviceLocator->get(DeliveryServiceFactory::class);
        $log = $serviceLocator->get(LogFactory::class);
        $authService = $serviceLocator->get(AuthenticationServiceFactory::class);
        $accountContainer = $serviceLocator->get(AccountContainerFactory::class);
        return new EmailController(
            $entityLoader,
            $senderService,
            $deliveryService,
            $log,
            $authService,
            $accountContainer
        );
    }
}
