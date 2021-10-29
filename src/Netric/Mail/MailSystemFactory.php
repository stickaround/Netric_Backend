<?php

declare(strict_types=1);

namespace Netric\Mail;

use Netric\Account\AccountContainerFactory;
use Netric\Config\ConfigFactory;
use Netric\Mail\DataMapper\MailDataMapperFactory;
use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use RuntimeException;

/**
 * Create a service for interacting with the mailsystem
 */
class MailSystemFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return DeliveryService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $accountContainer = $serviceLocator->get(AccountContainerFactory::class);
        $config = $serviceLocator->get(ConfigFactory::class);

        if (empty($config->localhost_root)) {
            throw new RuntimeException('No config value was found for localhost_root, this is a required param');
        }

        return new MailSystem(
            $config->localhost_root,
            $accountContainer,
            $serviceLocator->get(MailDataMapperFactory::class)
        );
    }
}
