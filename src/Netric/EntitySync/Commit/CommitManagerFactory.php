<?php

declare(strict_types=1);

namespace Netric\EntitySync\Commit;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\EntitySync\Commit\DataMapper\DataMapperFactory;

/**
 * Create a Entity Sync Commit Manager service
 *
 * @package Netric\EntitySync\Commit\CommitManager
 */
class CommitManagerFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return CommitManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $dm = $serviceLocator->get(DataMapperFactory::class);
        return new CommitManager($dm);
    }
}
