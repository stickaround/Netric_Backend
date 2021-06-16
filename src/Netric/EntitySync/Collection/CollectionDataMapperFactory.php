<?php

declare(strict_types=1);

namespace Netric\EntitySync\Collection;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Db\Relational\RelationalDbContainerFactory;
use Netric\ServiceManager;

/**
 * Create a Entity Sync Commit DataMapper service
 */
class CollectionDataMapperFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return CollectionDataMapperInterface
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $relationalDbCon = $serviceLocator->get(RelationalDbContainerFactory::class);

        return new CollectionRdbDataMapper($relationalDbCon);
    }
}
