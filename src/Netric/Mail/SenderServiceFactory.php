<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\Mail;

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
        $transport = $serviceLocator->get(TransportFactory::class);
        $bulkTransport = $serviceLocator->get(BulkTransportFactory::class);
        $log = $serviceLocator->get(LogFactory::class);
        return new SenderService($transport, $bulkTransport, $log);
    }
}
