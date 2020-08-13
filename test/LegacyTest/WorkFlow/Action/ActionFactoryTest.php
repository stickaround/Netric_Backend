<?php
/**
 * Test the ActionFactory class
 */
namespace NetricTest\WorkFlow\Action;

use PHPUnit\Framework\TestCase;
use Netric\WorkFlow\Action\ActionExecutorFactory;
use Netric\WorkFlow\Action\Exception\ActionNotFoundException;
use Netric\WorkFlow\Action\TestActionExecutor;

class ActionFactoryTest extends TestCase
{
    /**
     * Reference to account running for unit tests
     *
     * @var \Netric\Account\Account
     */
    private $account = null;

    /**
     * Action factory for testing
     *
     * @var ActionExecutorFactory
     */
    private $actionFactory = null;

    protected function setUp(): void
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
        $sl = $this->account->getServiceManager();
        $this->actionFactory = new ActionExecutorFactory($sl);
    }

    /**
     * Make sure we can construct an action by name
     */
    public function testCreate()
    {
        $testAction = $this->actionFactory->create("test");
        $this->assertInstanceOf(TestActionExecutor::class, $testAction);
    }

    /**
     * Check that trying to load a non-existing actions results in an exception
     */
    public function testCreateNotFound()
    {
        $this->expectException(ActionNotFoundException::class);
        $this->actionFactory->create("none-existing-action");
    }
}
