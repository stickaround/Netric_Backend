<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */

namespace Netric\Mail\Transport;

use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\ServiceManager\ServiceFactoryInterface;

/**
 * Create a new Transport service based on account settings for bulk email
 */
class BulkTransportFactory implements ServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceManager ServiceLocator for injecting dependencies
     * @return TransportInterface
     */
    public function createService(ServiceLocatorInterface $serviceManager)
    {
        // Get the required method
        $config = $serviceManager->get("Config");
        $transportMode = $config->email['mode'];

        // Create transport variable to set
        $transport = null;

        /*
         * If email is being suppressed via a config param, then return InMemory transport
         * so we do not try to send out emails in a development/test environment.
         */
        if ($config->email['supress'])
        {
            return new InMemory();
        }

        // Call the factory to return simple transports
        switch ($transportMode)
        {
            case 'smtp':
                return $serviceManager->get("Netric/Mail/Transport/BulkSmtp");
            case 'in-memory':
                return new InMemory();
            case 'sendmail':
                return new Sendmail();
        }

        throw new Exception\InvalidArgumentException("No transport for method " . $transportMode);
    }
}
