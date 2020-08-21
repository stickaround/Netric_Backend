<?php

declare(strict_types=1);

namespace Netric\Account\Billing;

use Netric\Config\ConfigFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\Log\LogFactory;
use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\PaymentGateway\SystemPaymentGatewayFactory;

class AccountBillingServiceFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return void
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get(ConfigFactory::class);
        return new AccountBillingService(
            $serviceLocator->get(LogFactory::class),
            $serviceLocator->get(EntityLoaderFactory::class),
            $serviceLocator->get(LogFactory::class),
            $config->main_account_id,
            $serviceLocator->get(SystemPaymentGatewayFactory::class),
            $serviceLocator->get(IndexFactory::class)
        );
    }
}
