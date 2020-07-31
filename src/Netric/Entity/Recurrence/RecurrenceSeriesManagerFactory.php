<?php

namespace Netric\Entity\Recurrence;

use Netric\ServiceManager\AccountServiceFactoryInterface;
use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\Entity\DataMapper\EntityDataMapperFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;

/**
 * Create a new Recurring Entity Series Writer service
 */
class RecurrenceSeriesManagerFactory implements AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return RecurrenceSeriesManager
     */
    public function createService(AccountServiceManagerInterface $sl)
    {
        $recurIdentityMapper = $sl->get(RecurrenceIdentityMapperFactory::class);
        $entityLoader = $sl->get(EntityLoaderFactory::class);
        $entityDataMapper = $sl->get(EntityDataMapperFactory::class);
        $entityIndex = $sl->get(IndexFactory::class);
        $entityDefinitionLoader = $sl->get(EntityDefinitionLoaderFactory::class);
        return new RecurrenceSeriesManager(
            $recurIdentityMapper,
            $entityLoader,
            $entityDataMapper,
            $entityIndex,
            $entityDefinitionLoader
        );
    }
}
