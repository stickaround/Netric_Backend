<?php

declare(strict_types=1);

namespace Netric\Handler;

use Netric\Entity\EntityLoaderFactory;
use Netric\Entity\Notifier\NotifierFactory;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\ServiceManager\ApplicationServiceFactoryInterface;

/**
 * Construct the chat hanlder
 */
class ChatHandlerFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Construct a controller and return it
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return ChatHandler
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        $notifier = $serviceLocator->get(NotifierFactory::class);
        return new ChatHandler($entityLoader, $notifier);
    }
}
