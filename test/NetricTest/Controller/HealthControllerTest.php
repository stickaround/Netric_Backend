<?php
namespace NetricTest\Controller;

use PHPUnit\Framework\TestCase;
use Netric\Request\HttpRequest;
use Netric\Controller\HealthController;
use Netric\Application\Health\HealthCheckInterface;
use Netric\Log\LogInterface;

/**
 * Test self-checking validation actions
 *
 * @group integration
 */
class HealthControllerTest extends TestCase
{
    /**
     * Initialized controller with mock dependencies
     */
    private HealthController $healthController;

    /**
     * Dependency mocks
     */
    private HealthCheckInterface $healthCheck;
    private LogInterface $mockLog;

    /**
     * Construct the controller
     *
     * @return void
     */
    protected function setUp(): void
    {
        // Create mocks
        $this->healthCheck = $this->createMock(HealthCheckInterface::class);
        $this->mockLog = $this->createMock(LogInterface::class);

        // Create the controller with mocks
        $this->healthController = new HealthController(
            $this->healthCheck,
            $this->mockLog
        );
        $this->healthController->testMode = true;
    }

    /**
     * Test ping
     */
    public function testGetPingAction()
    {
        $request = new HttpRequest();
        $response = $this->healthController->getPingAction($request);
        $this->assertEquals(200, $response->getReturnCode());
    }

    /**
     * Test console health test
     */
    public function testConsoleTestAction()
    {
        $this->healthCheck->method('isSystemHealthy')->willReturn(true);

        $response = $this->healthController->consoleTestAction();
        $this->assertEquals(['SUCCESS: The system is ok'], $response->getOutputBuffer());
    }

    /**
     * Test console dependencies test
     */
    public function testConsoleTestDependenciesAction()
    {
        $this->healthCheck->method('areDependenciesLive')->willReturn(true);

        $response = $this->healthController->consoleTestDependenciesAction();
        $this->assertEquals(['SUCCESS: Critical dependencies are live'], $response->getOutputBuffer());
    }
}
