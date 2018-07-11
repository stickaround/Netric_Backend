<?php
namespace Netric\EntityQuery\Plugin;

use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\EntityQuery;
use Netric\Entity\Recurrence\RecurrenceSeriesManager;

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
     * @param AccountServiceManagerInterface $sl A service locator for getting dependencies
     * @param EntityQuery $query The query being executed
     * @return bool true on success, false on failure
     */
    public function onBeforeExecuteQuery(AccountServiceManagerInterface $sl, EntityQuery $query)
    {
        $recurSeriesManager = $sl->get(RecurrenceSeriesManager::class);

        // Check to see if we have any recurring patterns to update based on this query
        $recurSeriesManager->createInstancesFromQuery($query);
    }

    /**
     * Perform an operation after a query is executed
     *
     * @param AccountServiceManagerInterface $sl A service locator for getting dependencies
     * @param EntityQuery $query The query being executed
     * @return bool true on success, false on failure
     */
    public function onAfterExecuteQuery(AccountServiceManagerInterface $sl, EntityQuery $query)
    {
        // Nothing to do after the query is executed for now
    }
}
