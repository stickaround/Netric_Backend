<?php
/**
 * Return the identity mapper service for recurrence patterns
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\Entity\Recurrence;

use Netric\ServiceManager;

/**
 * Create a new recurrence indentity mapper service
 *
 * @package RecurrenceIdentityMapper
 */
class RecurrenceIdentityMapperFactory implements ServiceManager\AccountServiceLocatorInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceManager\AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return RecurrenceIdentityMapper
     */
    public function createService(ServiceManager\AccountServiceManagerInterface $sl)
    {
        $recurDataMapper = $sl->get("RecurrenceDataMapper");
        return new RecurrenceIdentityMapper($recurDataMapper);
    }
}
