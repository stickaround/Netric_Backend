<?php

declare(strict_types=1);

namespace Netric\EntitySync\Commit;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\EntitySync\Commit\DataMapper\DataMapperFactory;

/**
 * Create a Entity Sync Commit Manager service
 *
 * @package Netric\EntitySync\Commit\CommitManager
 */
class CommitManagerFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return CommitManager
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        $dm = $serviceLocator->get(DataMapperFactory::class);
        return new CommitManager($dm);
    }
}
