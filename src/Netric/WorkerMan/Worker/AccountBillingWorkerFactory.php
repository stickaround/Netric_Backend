<?php

declare(strict_types=1);

namespace Netric\WorkerMan\Worker;

use Aereus\ServiceContainer\ServiceContainerInterface;

/**
 * Construct the factory that handles billing for each account
 */
class AccountBillingWorkerFactory
{
    /**
     * Worker
     *
     * @param ServiceContainerInterface $serviceLocator For injecting dependencies
     * @return EntityPostSaveWorker
     */
    public function create(ServiceContainerInterface $serviceLocator)
    {
        return new AccountBillingWorker($serviceLocator->getApplication());
    }
}
