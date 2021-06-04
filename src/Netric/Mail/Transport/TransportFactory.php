<?php
namespace Netric\Mail\Transport;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Config\ConfigFactory;
use Netric\Mail\Transport\SmtpFactory;

/**
 * Create the default SMTP transport
 */
class TransportFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return TransportInterface
     * @throws Exception\InvalidArgumentException if a transport could not be created
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        // Get the required method
        $config = $serviceLocator->get(ConfigFactory::class);
        $transportMode = $config->email['mode'];

        // Create transport variable to set
        $transport = null;

        /*
         * If email is being suppressed via a config param, then return InMemory transport
         * so we do not try to send out emails in a development/test environment.
         */
        if (isset($config->email['supress']) && $config->email['supress']) {
            return new InMemory();
        }

        // Call the factory to return simple transports
        switch ($transportMode) {
            case 'smtp':
                return $serviceLocator->get(SmtpFactory::class);
            case 'in-memory':
                return new InMemory();
            case 'sendmail':
                return new Sendmail();
        }

        throw new Exception\InvalidArgumentException("No transport for method " . $transportMode);
    }
}
