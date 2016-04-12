<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\EntityQuery\Plugin;

use Netric\ServiceManager\ServiceLocatorInterface;

/**
 * Interface describes objType specific plugins to be considered when running queries
 */
interface PluginInterface
{
    /**
     * Perform an operation before a query is executed
     *
     * @param ServiceLocatorInterface $sl A service locator for getting dependencies
     * @return bool true on success, false on failure
     */
    public function onBeforeExecuteQuery(ServiceLocatorInterface $sl);

    /**
     * Perform an operation after a query is executed
     *
     * @param ServiceLocatorInterface $sl A service locator for getting dependencies
     * @return bool true on success, false on failure
     */
    public function onAfterExecuteQuery(ServiceLocatorInterface $sl);
}