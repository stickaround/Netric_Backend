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
use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;

/**
 * Create a new Recurring DataMapper service
 */
class RecurrenceDataMapperFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return RecurrenceDataMapper
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        $relationalDbCon = $serviceLocator->get(RelationalDbContainerFactory::class);
        $entityDefinitionLoader = $serviceLocator->get(EntityDefinitionLoaderFactory::class);

        return new RecurrenceRdbDataMapper($relationalDbCon, $entityDefinitionLoader);
    }
}
