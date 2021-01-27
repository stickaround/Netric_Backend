<?php

/**
 * Service factory for the recurrence datamapper
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */

namespace Netric\Entity\Recurrence;

use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\Db\Relational\RelationalDbContainerFactory;
use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;

/**
 * Create a new Recurring DataMapper service
 */
class RecurrenceDataMapperFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return RecurrenceDataMapper
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $relationalDbCon = $serviceLocator->get(RelationalDbContainerFactory::class);
        $entityDefinitionLoader = $serviceLocator->get(EntityDefinitionLoaderFactory::class);

        return new RecurrenceRdbDataMapper($relationalDbCon, $entityDefinitionLoader);
    }
}
