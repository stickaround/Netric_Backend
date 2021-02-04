<?php

namespace Netric\Entity\Recurrence;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\Entity\DataMapper\EntityDataMapperFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;

/**
 * Create a new Recurring Entity Series Writer service
 */
class RecurrenceSeriesManagerFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return RecurrenceSeriesManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $recurIdentityMapper = $serviceLocator->get(RecurrenceIdentityMapperFactory::class);
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        $entityDataMapper = $serviceLocator->get(EntityDataMapperFactory::class);
        $entityIndex = $serviceLocator->get(IndexFactory::class);
        $entityDefinitionLoader = $serviceLocator->get(EntityDefinitionLoaderFactory::class);
        return new RecurrenceSeriesManager(
            $recurIdentityMapper,
            $entityLoader,
            $entityDataMapper,
            $entityIndex,
            $entityDefinitionLoader
        );
    }
}
