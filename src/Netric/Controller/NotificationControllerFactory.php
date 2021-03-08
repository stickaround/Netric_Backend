<?php

namespace Netric\Controller;

use Netric\Mvc\ControllerFactoryInterface;
use Netric\Mvc\ControllerInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Account\AccountContainerFactory;
use Netric\Authentication\AuthenticationServiceFactory;
use Netric\Account\Module\ModuleServiceFactory;
use Netric\Account\Billing\AccountBillingServiceFactory;
use Netric\PaymentGateway\SystemPaymentGatewayFactory;
use Netric\Entity\EntityLoaderFactory;

/**
 * Construct the notification controller
 */
class NotificationControllerFactory implements ControllerFactoryInterface
{
    /**
     * Construct a controller and return it
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return ControllerInterface
     */
    public function get(ServiceLocatorInterface $serviceLocator): ControllerInterface
    {
        return new NotificationController();
    }
}
