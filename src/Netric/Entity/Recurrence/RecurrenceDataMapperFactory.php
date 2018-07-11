<?php
/**
 * Service factory for the recurrence datamapper
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\Entity\Recurrence;

use Netric\Db\DbFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\ServiceManager;

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
        $entDefLoader = $sl->get(EntityDefinitionLoaderFactory::class);
        $dbh = $sl->get(DbFactory::class);
        return new RecurrenceDataMapper($sl->getAccount(), $dbh, $entDefLoader);
    }
}
