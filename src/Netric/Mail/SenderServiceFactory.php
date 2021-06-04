<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\Mail;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Mail\Transport\TransportFactory;
use Netric\Mail\Transport\BulkTransportFactory;
use Netric\Log\LogFactory;

/**
 * Create a service for sending email
 */
class SenderServiceFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return SenderService
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        $transport = $serviceLocator->get(TransportFactory::class);
        $bulkTransport = $serviceLocator->get(BulkTransportFactory::class);
        $log = $serviceLocator->get(LogFactory::class);
        return new SenderService($transport, $bulkTransport, $log);
    }
}
