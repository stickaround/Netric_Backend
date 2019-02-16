<?php
/**
 * Test the ActionFactory class
 */
namespace NetricTest\WorkFlow\Action;

use PHPUnit\Framework\TestCase;
use Netric\WorkFlow\Action\ActionFactory;
use Netric\WorkFlow\Action\Exception\ActionNotFoundException;
use Netric\WorkFlow\Action\TestAction;

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
     * @var ActionFactory
     */
    private $actionFactory = null;

    protected function setUp(): void
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
        $sl = $this->account->getServiceManager();
        $this->actionFactory = new ActionFactory($sl);
    }

    /**
     * Make sure we can construct an action by name
     */
    public function testCreate()
    {
        $testAction = $this->actionFactory->create("test");
        $this->assertInstanceOf(TestAction::class, $testAction);
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
