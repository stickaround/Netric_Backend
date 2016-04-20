<?php
/**
 * Service factory for the recurrence datamapper
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\Entity\Recurrence;

use Netric\ServiceManager;

/**
 * Create a new Recurring DataMapper service
 *
 * @package Netric\FileSystem
 */
class RecurrenceDataMapperFactory implements ServiceManager\AccountServiceLocatorInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceManager\AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return RecurrenceDataMapper
     */
    public function createService(ServiceManager\AccountServiceManagerInterface $sl)
    {
        $entDefLoader = $sl->get("EntityDefinitionLoader");
        $dbh = $sl->get("Db");
        return new RecurrenceDataMapper($sl->getAccount(), $dbh, $entDefLoader);
    }
}
