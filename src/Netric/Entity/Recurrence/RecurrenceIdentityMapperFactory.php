<?php
/**
 * Return the identity mapper service for recurrence patterns
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\Entity\Recurrence;

use Netric\Db\Relational\RelationalDbContainerFactory;
use Netric\ServiceManager\AccountServiceFactoryInterface;
use Netric\ServiceManager\AccountServiceManagerInterface;

/**
 * Create a new recurrence indentity mapper service
 */
class RecurrenceIdentityMapperFactory implements AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param AccountServiceManagerInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return RecurrenceIdentityMapper
     */
    public function createService(AccountServiceManagerInterface $serviceLocator)
    {
        $recurrenceDataMapper = $serviceLocator->get(RecurrenceDataMapperFactory::class);
        return new RecurrenceIdentityMapper($recurrenceDataMapper);
    }
}
