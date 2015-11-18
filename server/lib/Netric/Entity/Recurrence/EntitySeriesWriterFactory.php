<?php
/**
 * Service factory for the entity series writer
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
class EntitySeriesWriterFactory implements ServiceManager\ServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceManager\ServiceLocatorInterface $sl ServiceLocator for injecting dependencies
     * @return EntitySeriesWriter
     */
    public function createService(ServiceManager\ServiceLocatorInterface $sl)
    {
        $recurDataMapper = $sl->get("RecurrenceDataMapper");
        $recurIdentityMapper = $sl->get("RecurrenceIdentityMapper");
        return new EntitySeriesWriter($recurDataMapper, $recurIdentityMapper);
    }
}
