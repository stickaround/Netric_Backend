<?php

/**
 * Example plugin to use in unit tests
 */

namespace NetricTest\EntityQuery\Index\TestAssets;

use Netric\EntityQuery\Plugin\PluginInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\EntityQuery\EntityQuery;

class TestIndexPlugin implements PluginInterface
{
    /**
     * Flag to indicate the onBeforeExecuteQuery ran
     *
     * @var bool
     */
    public $beforeRan = false;

    /**
     * Flag to indicate if the onAfterExecuteQuery ran
     *
     * @var bool
     */
    public $afterRan = false;

    /**
     * Perform an operation before a query is executed
     *
     * @param ServiceLocatorInterface $serviceLocator A service locator for getting dependencies
     * @param EntityQuery $query The query being executed
     * @return bool true on success, false on failure
     */
    public function onBeforeExecuteQuery(ServiceLocatorInterface $serviceLocator, EntityQuery $query)
    {
        $this->beforeRan = true;
    }

    /**
     * Perform an operation after a query is executed
     *
     * @param ServiceLocatorInterface $serviceLocator A service locator for getting dependencies
     * @param EntityQuery $query The query being executed
     * @return bool true on success, false on failure
     */
    public function onAfterExecuteQuery(ServiceLocatorInterface $serviceLocator, EntityQuery $query)
    {
        $this->afterRan = true;
    }
}
