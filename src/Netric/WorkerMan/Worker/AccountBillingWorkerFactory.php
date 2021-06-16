<?php

declare(strict_types=1);

namespace Netric\WorkerMan\Worker;

use Netric\ServiceManager\ServiceLocatorInterface;

/**
 * Construct the factory that handles billing for each account
 */
class AccountBillingWorkerFactory
{
    /**
     * Worker
     *
     * @param ServiceLocatorInterface $serviceLocator For injecting dependencies
     * @return EntityPostSaveWorker
     */
    public function create(ServiceLocatorInterface $serviceLocator)
    {
        return new AccountBillingWorker($serviceLocator->getApplication());
    }
}
