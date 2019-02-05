<?php
namespace Netric\Controller;

use Netric\Mvc\ControllerFactoryInterface;
use Netric\Mvc\ControllerInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Mail\SenderServiceFactory;
use Netric\Entity\EntityLoaderFactory;

/**
 * Construct the EmailController for itneracting with email messages
 */
class EmailControllerFactory implements ControllerFactoryInterface
{
    /**
     * Construct a controller and return it
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return ControllerInterface
     */
    public function get(ServiceLocatorInterface $serviceLocator): ControllerInterface
    {
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        $senderService = $serviceLocator->get(SenderServiceFactory::class);
        return new EmailController($entityLoader, $senderService);
    }
}
