<?php

namespace Netric\Entity\Recurrence;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\Entity\DataMapper\EntityDataMapperFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;

/**
 * Create a new Recurring Entity Series Writer service
 */
class RecurrenceSeriesManagerFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return RecurrenceSeriesManager
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
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
