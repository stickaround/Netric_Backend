<?php
/**
 * Return the identity mapper service for recurrence patterns
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\Entity\Recurrence;

use Netric\Db\Relational\RelationalDbContainerFactory;
use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;

/**
 * Create a new recurrence indentity mapper service
 */
class RecurrenceIdentityMapperFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return RecurrenceIdentityMapper
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        $recurrenceDataMapper = $serviceLocator->get(RecurrenceDataMapperFactory::class);
        return new RecurrenceIdentityMapper($recurrenceDataMapper);
    }
}
