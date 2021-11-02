<?php

namespace Netric\Mail;

use Netric\Config\ConfigFactory;
use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Mail\Transport\TransportFactory;
use Netric\Mail\Transport\BulkTransportFactory;
use Netric\Log\LogFactory;

/**
 * Create a service for sending email
 */
class SenderServiceFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return SenderService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        // TODO: we really need to fix this
        $log = $serviceLocator->get(LogFactory::class);
        $config = $serviceLocator->get(ConfigFactory::class);
        return new SenderService($log, $config->email);
    }
}
