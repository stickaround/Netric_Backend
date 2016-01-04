<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */

namespace Netric\Mail;

use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\ServiceManager\ServiceFactoryInterface;

/**
 * Create an EntityEmailer service for sending email message entities
 */
class EntityMailerFactory implements ServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceManager ServiceLocator for injecting dependencies
     * @return TransportInterface
     * @throws Exception\InvalidArgumentException if a transport could not be created
     */
    public function createService(ServiceLocatorInterface $serviceManager)
    {
        return new EntityMailer();
    }
}
