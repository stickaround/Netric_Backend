<?php

declare(strict_types=1);

namespace Netric\Entity\Notifier\Sender;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Entity\EntityLoaderFactory;
use Netric\Log\LogFactory;
use Netric\Mail\MailSystemFactory;
use Netric\Mail\SenderServiceFactory;

/**
 * Create a new service
 */
class PublicUserEmailSenderFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceManager ServiceLocator for injecting dependencies
     * @return Notifier
     */
    public function createService(ServiceLocatorInterface $serviceManager)
    {
        $entityLoader = $serviceManager->get(EntityLoaderFactory::class);
        $mailSender = $serviceManager->get(SenderServiceFactory::class);
        $mailSystem = $serviceManager->get(MailSystemFactory::class);
        $log = $serviceManager->get(LogFactory::class);
        return new PublicUserEmailSender($entityLoader, $mailSender, $mailSystem, $log);
    }
}
