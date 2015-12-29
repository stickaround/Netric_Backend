<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */

namespace Netric\Mail\Transport;

use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\ServiceManager\ServiceFactoryInterface;

/**
 * Create a new Transport service based on account settings
 */
class TransportFactory implements ServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceManager ServiceLocator for injecting dependencies
     * @return TransportInterface
     */
    public function createService(ServiceLocatorInterface $serviceManager)
    {
        // TODO: return type of transport to use for this account
    }
}
