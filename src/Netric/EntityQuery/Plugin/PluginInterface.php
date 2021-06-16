<?php

/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2016 Aereus
 */

namespace Netric\EntityQuery\Plugin;

use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\EntityQuery\EntityQuery;

/**
 * Interface describes objType specific plugins to be considered when running queries
 */
interface PluginInterface
{
    /**
     * Perform an operation before a query is executed
     *
     * @param ServiceLocatorInterface $serviceLocator A service locator for getting dependencies
     * @param EntityQuery $query The query being executed
     * @return bool true on success, false on failure
     */
    public function onBeforeExecuteQuery(ServiceLocatorInterface $serviceLocator, EntityQuery $query);

    /**
     * Perform an operation after a query is executed
     *
     * @param ServiceLocatorInterface $serviceLocator A service locator for getting dependencies
     * @param EntityQuery $query The query being executed
     * @return bool true on success, false on failure
     */
    public function onAfterExecuteQuery(ServiceLocatorInterface $serviceLocator, EntityQuery $query);
}
