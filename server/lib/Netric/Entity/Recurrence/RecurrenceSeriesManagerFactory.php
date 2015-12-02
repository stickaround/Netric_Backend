<?php
/**
 * Service factory for the entity series manager
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\Entity\Recurrence;

use Netric\ServiceManager;

/**
 * Create a new Recurring Entity Series Writer service
 *
 * @package Netric\FileSystem
 */
class RecurrenceSeriesManagerFactory implements ServiceManager\ServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceManager\ServiceLocatorInterface $sl ServiceLocator for injecting dependencies
     * @return EntitySeriesWriter
     */
    public function createService(ServiceManager\ServiceLocatorInterface $sl)
    {
        $recurIdentityMapper = $sl->get("RecurrenceIdentityMapper");
        $entityLoader = $sl->get("EntityLoader");
        $entityDataMapper = $sl->get("Entity_DataMapper");
        $entityIndex = $sl->get("EntityQuery_Index");
        $entityDefinitionLoader = $sl->get("EntityDefinitionLoader");
        return new RecurrenceSeriesManager(
            $recurIdentityMapper,
            $entityLoader,
            $entityDataMapper,
            $entityIndex,
            $entityDefinitionLoader
        );
    }
}
