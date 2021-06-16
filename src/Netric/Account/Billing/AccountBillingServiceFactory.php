<?php

declare(strict_types=1);

namespace Netric\Account\Billing;

use Netric\Config\ConfigFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\Log\LogFactory;
use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\PaymentGateway\SystemPaymentGatewayFactory;

class AccountBillingServiceFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceContainerInterface $serviceLocator
     * @return void
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        $config = $serviceLocator->get(ConfigFactory::class);
        return new AccountBillingService(
            $serviceLocator->get(LogFactory::class),
            $serviceLocator->get(EntityLoaderFactory::class),
            $config->main_account_id,
            $serviceLocator->get(SystemPaymentGatewayFactory::class),
            $serviceLocator->get(IndexFactory::class)
        );
    }
}
