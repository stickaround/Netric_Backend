<?php
namespace NetricTest\Controller;

use PHPUnit\Framework\TestCase;
use Netric\Controller\HealthController;

/**
 * Test self-checking validation actions
 *
 * @group integration
 */
class HealthControllerTest extends TestCase
{
    /**
     * Constructed controller
     *
     * @var HealthController
     */
    private $controller = null;

    /**
     * Construct the controller
     *
     * @return void
     */
    protected function setUp()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        // Rest stats for logs since erros may have occurred before this test
        $account->getApplication()->getLog()->resetLevelStats();
        $this->controller = new HealthController($account->getApplication(), $account);
        $this->controller->testMode = true;
    }

    /**
     * Test ping
     */
    public function testGetPingAction()
    {
        $response = $this->controller->getPingAction();
        $this->assertEquals(200, $response->getReturnCode());
    }

    /**
     * Test console health test
     */
    public function testConsoleTestAction()
    {
        $response = $this->controller->consoleTestAction();
        // Code 0 = success from the console
        $this->assertEquals(0, $response->getReturnCode());
    }
}
