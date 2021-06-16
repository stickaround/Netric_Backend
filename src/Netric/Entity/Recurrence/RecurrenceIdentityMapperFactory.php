<?php
/**
 * Return the identity mapper service for recurrence patterns
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\Entity\Recurrence;

use Netric\Db\Relational\RelationalDbContainerFactory;
use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;

/**
 * Create a new recurrence indentity mapper service
 */
class RecurrenceIdentityMapperFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return RecurrenceIdentityMapper
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $recurrenceDataMapper = $serviceLocator->get(RecurrenceDataMapperFactory::class);
        return new RecurrenceIdentityMapper($recurrenceDataMapper);
    }
}
