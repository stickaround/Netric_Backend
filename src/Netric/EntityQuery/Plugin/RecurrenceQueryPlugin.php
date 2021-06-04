<?php

namespace Netric\EntityQuery\Plugin;

use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\EntityQuery\EntityQuery;
use Netric\Entity\Recurrence\RecurrenceSeriesManagerFactory;

/**
 * Plugin used to work with the recurrence series manager to
 * create any recurrence instances in a series prior to a query running.
 *
 * This gives us the ability to create recurring instances just in time as
 * they are asked for via the query rather than trying to create instances for
 * all time on save.
 */
class RecurrenceQueryPlugin
{
    /**
     * Use the RecurrenceSeriesManager to create instances from soon-to-be-run query
     *
     * @param ServiceContainerInterface $serviceLocator A service locator for getting dependencies
     * @param EntityQuery $query The query being executed
     * @return bool true on success, false on failure
     */
    public function onBeforeExecuteQuery(ServiceContainerInterface $serviceLocator, EntityQuery $query)
    {
        $recurSeriesManager = $serviceLocator->get(RecurrenceSeriesManagerFactory::class);

        // Check to see if we have any recurring patterns to update based on this query
        $recurSeriesManager->createInstancesFromQuery($query);
    }

    /**
     * Perform an operation after a query is executed
     *
     * @param ServiceContainerInterface $serviceLocator A service locator for getting dependencies
     * @param EntityQuery $query The query being executed
     * @return bool true on success, false on failure
     */
    public function onAfterExecuteQuery(ServiceContainerInterface $serviceLocator, EntityQuery $query)
    {
        // Nothing to do after the query is executed for now
    }
}
