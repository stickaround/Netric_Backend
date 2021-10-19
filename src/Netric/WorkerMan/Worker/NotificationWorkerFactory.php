<?php

declare(strict_types=1);

namespace Netric\WorkerMan\Worker;

use Netric\Entity\EntityLoader;
use Netric\Entity\Notifier\NotifierFactory;
use Netric\Log\LogFactory;
use Netric\ServiceManager\ServiceLocatorInterface;

/**
 * Construct worker called after each entity save
 */
class NotificationWorkerFactory
{
    /**
     * Entity creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator For injecting dependencies
     * @return NotificationWorker
     */
    public function create(ServiceLocatorInterface $serviceLocator)
    {
        $notifier = $serviceLocator->get(NotifierFactory::class);
        $entityLoader = $serviceLocator->get(EntityLoader::class);
        $log = $serviceLocator->get(LogFactory::class);
        return new NotificationWorker($notifier, $entityLoader, $log);
    }
}
