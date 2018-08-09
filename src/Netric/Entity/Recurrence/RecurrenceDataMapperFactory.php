<?php
/**
 * Service factory for the recurrence datamapper
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\Entity\Recurrence;

use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\ServiceManager;
use Netric\Db\Relational\RelationalDbFactory;

/**
 * Create a new Recurring DataMapper service
 */
class RecurrenceDataMapperFactory implements ServiceManager\AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceManager\AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return RecurrenceDataMapper
     */
    public function createService(ServiceManager\AccountServiceManagerInterface $sl)
    {
        $entityDefinitionLoader = $sl->get(EntityDefinitionLoaderFactory::class);
        $database = $sl->get(RelationalDbFactory::class);
        return new RecurrenceRdbDataMapper($database, $entityDefinitionLoader);
    }
}
