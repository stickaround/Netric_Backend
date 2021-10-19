<?php

namespace Netric\Mail;

use Netric\Account\AccountContainerFactory;
use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\EntityGroupings\GroupingLoaderFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\FileSystem\FileSystemFactory;
use Netric\Log\LogFactory;
use Netric\Mail\Maildrop\MaildropContainerFactory;

/**
 * Create a service for delivering mail
 */
class DeliveryServiceFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return DeliveryService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        $maildropContainer = $serviceLocator->get(MaildropContainerFactory::class);
        $groupingsLoader = $serviceLocator->get(GroupingLoaderFactory::class);
        $log = $serviceLocator->get(LogFactory::class);
        $index = $serviceLocator->get(IndexFactory::class);
        $mailSystem = $serviceLocator->get(MailSystemFactory::class);
        $accountContainer = $serviceLocator->get(AccountContainerFactory::class);

        return new DeliveryService(
            $mailSystem,
            $maildropContainer,
            $entityLoader,
            $index,
            $accountContainer
        );
    }
}
