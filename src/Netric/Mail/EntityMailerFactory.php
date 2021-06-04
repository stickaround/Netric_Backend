<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */

namespace Netric\Mail;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;

/**
 * Create an EntityEmailer service for sending email message entities
 */
class EntityMailerFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return TransportInterface
     * @throws Exception\InvalidArgumentException if a transport could not be created
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        return new EntityMailer();
    }
}
