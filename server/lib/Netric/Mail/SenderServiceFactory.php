<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\Mail;

use Netric\ServiceManager;

/**
 * Create a service for sending email
 */
class SenderServiceFactory implements ServiceManager\ServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceManager\ServiceLocatorInterface $sl ServiceLocator for injecting dependencies
     * @return SenderService
     */
    public function createService(ServiceManager\ServiceLocatorInterface $sl)
    {
        return new SenderService();
    }
}
