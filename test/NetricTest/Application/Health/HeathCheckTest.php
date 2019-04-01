<?php
namespace NetricTest\Application\Health;

use Netric\Application\Health\HealthCheck;
use Netric\Log\LogInterface;
use Netric\Application\Health\DependencyCheck\DependencyCheckInterface;
use PHPUnit\Framework\TestCase;

/**
 * Check the health of the backend server
 */
class HeathCheckTest extends TestCase
{
    /**
     * Make sure that we are healthy if there are no logs
     */
    public function testIsApplicationHealthy()
    {
        $mockLog = $this->getMockBuilder(LogInterface::class)->getMock();
        $mockLog->method('getLevelStats')->willReturn([]);

        $dependency = $this->getMockBuilder(DependencyCheckInterface::class)->getMock();
        $dependency->method('isAvailable')->willReturn(true);

        $healthCheck = new HealthCheck($mockLog, [$dependency]);
        $this->assertTrue($healthCheck->isApplicationHealthy());
    }

    /**
     * Test that the application will be marked as unhealthy if there are 4 errors since
     * the last time the check was run.
     */
    public function testIsApplicationHealthyFailWithErrors()
    {
        // This will fail because the default max number of allowed errors is 3
        $mockLog = $this->getMockBuilder(LogInterface::class)->getMock();
        $mockLog->method('getLevelStats')->willReturn(['error'=>4]);

        $dependency = $this->getMockBuilder(DependencyCheckInterface::class)->getMock();
        $dependency->method('isAvailable')->willReturn(true);

        $healthCheck = new HealthCheck($mockLog, [$dependency]);
        $this->assertFalse($healthCheck->isApplicationHealthy());
    }

    /**
     * Test that the application will be marked as unhealthy if there was 1 critical log
     */
    public function testIsApplicationHealthyFailWithCritical()
    {
        // This will fail because the default max number of allowed errors is 3
        $mockLog = $this->getMockBuilder(LogInterface::class)->getMock();
        $mockLog->method('getLevelStats')->willReturn(['critical'=>1]);

        $dependency = $this->getMockBuilder(DependencyCheckInterface::class)->getMock();
        $dependency->method('isAvailable')->willReturn(true);

        $healthCheck = new HealthCheck($mockLog, [$dependency]);
        $this->assertFalse($healthCheck->isApplicationHealthy());
    }

    /**
     * Make sure under golden path everything is healthy
     */
    public function testIsSystemHealthy()
    {
        $mockLog = $this->getMockBuilder(LogInterface::class)->getMock();
        $mockLog->method('getLevelStats')->willReturn([]);

        $dependency = $this->getMockBuilder(DependencyCheckInterface::class)->getMock();
        $dependency->method('isAvailable')->willReturn(true);

        $healthCheck = new HealthCheck($mockLog, [$dependency]);
        $this->assertTrue($healthCheck->isSystemHealthy());
    }

    /**
     * Make sure we fail if the file store cannot be contacted
     */
    public function testIsSystemHealthyFail()
    {
        $mockLog = $this->getMockBuilder(LogInterface::class)->getMock();
        $mockLog->method('getLevelStats')->willReturn([]);

        $dependency = $this->getMockBuilder(DependencyCheckInterface::class)->getMock();
        $dependency->method('isAvailable')->willReturn(true);

        // Simulate inability to connect
        $dependency2 = $this->getMockBuilder(DependencyCheckInterface::class)->getMock();
        $dependency2->method('isAvailable')->willReturn(false);

        $healthCheck = new HealthCheck($mockLog, [$dependency, $dependency2]);
        $this->assertFalse($healthCheck->isSystemHealthy());
    }

    /**
     * Make sure under golden path everything is healthy
     */
    public function testAreDependenciesLive()
    {
        $mockLog = $this->getMockBuilder(LogInterface::class)->getMock();
        $mockLog->method('getLevelStats')->willReturn([]);

        $dependency = $this->getMockBuilder(DependencyCheckInterface::class)->getMock();
        $dependency->method('isAvailable')->willReturn(true);

        $healthCheck = new HealthCheck($mockLog, [$dependency]);
        $this->assertTrue($healthCheck->areDependenciesLive());
    }

    /**
     * Make sure we fail if the file store cannot be contacted
     */
    public function testAreDependenciesLiveFileStoreFail()
    {
        $mockLog = $this->getMockBuilder(LogInterface::class)->getMock();
        $mockLog->method('getLevelStats')->willReturn([]);

        $dependency = $this->getMockBuilder(DependencyCheckInterface::class)->getMock();
        $dependency->method('isAvailable')->willReturn(true);

        // Simulate inability to connect
        $dependency2 = $this->getMockBuilder(DependencyCheckInterface::class)->getMock();
        $dependency2->method('isAvailable')->willReturn(false);

        $healthCheck = new HealthCheck($mockLog, [$dependency, $dependency2]);
        $this->assertFalse($healthCheck->areDependenciesLive());
    }
}
