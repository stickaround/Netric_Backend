<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\Mail;

use Netric\ServiceManager;
use Netric\Mail\Transport\TransportFactory;
use Netric\Mail\Transport\BulkTransportFactory;
use Netric\Log\LogFactory;

/**
 * Create a service for sending email
 */
class SenderServiceFactory implements ServiceManager\AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceManager\AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return SenderService
     */
    public function createService(ServiceManager\AccountServiceManagerInterface $sl)
    {
        $transport = $sl->get(TransportFactory::class);
        $bulkTransport = $sl->get(BulkTransportFactory::class);
        $log = $sl->get(LogFactory::class);
        return new SenderService($transport, $bulkTransport, $log);
    }
}
