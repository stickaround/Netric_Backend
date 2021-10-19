<?php

declare(strict_types=1);

namespace Netric\Mail\Maildrop;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;

/**
 * Create a service for gettign maildrop drivers
 */
class MaildropContainerFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return DeliveryService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $maildrops = [
            $serviceLocator->get(MaildropEmailFactory::class),
            $serviceLocator->get(MaildropCommentFactory::class),
            $serviceLocator->get(MaildropTicketFactory::class)
        ];

        return new MaildropContainer($maildrops);
    }
}
