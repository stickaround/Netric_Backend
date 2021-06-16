<?php

declare(strict_types=1);

namespace Netric\PaymentGateway;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Config\ConfigFactory;
use Netric\Crypt\VaultServiceFactory;

/**
 * This is the payment gateway used for netric system charges like account billing
 */
class SystemPaymentGatewayFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceContainerInterface $sl
     * @return void
     */
    public function __invoke(ServiceContainerInterface $sl)
    {
        // Get secure keys from the vault (we never check these in)
        $vaultService = $sl->get(VaultServiceFactory::class);
        $apiTransactionKey = $vaultService->getSecret('anet_key');

        // Get regular config values
        $config = $sl->get(ConfigFactory::class);
        $apiLogin = $config->billing->anet_login;
        $url = $config->billing->anet_url;
        return new AuthDotNetGateway($apiLogin, $apiTransactionKey, $url);
    }
}
