<?php
/**
 * Example plugin to use in unit tests
 */
namespace NetricTest\EntityQuery\Index\TestAssets;

use Netric\EntityQuery\Plugin\PluginInterface;
use Netric\ServiceManager\AccountServiceManagerInterface;

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
     * @param AccountServiceManagerInterface $sl A service locator for getting dependencies
     * @return bool true on success, false on failure
     */
    public function onBeforeExecuteQuery(AccountServiceManagerInterface $sl)
    {
        $this->beforeRan = true;
    }

    /**
     * Perform an operation after a query is executed
     *
     * @param AccountServiceManagerInterface $sl A service locator for getting dependencies
     * @return bool true on success, false on failure
     */
    public function onAfterExecuteQuery(AccountServiceManagerInterface $sl)
    {
        $this->afterRan = true;
    }
}