<?php
namespace NetricTest\Application\Health;

use Netric\Application\Health\HealthCheck;
use Netric\Db\Relational\RelationalDbInterface;
use Netric\Log\LogInterface;
use Netric\FileSystem\FileStore\FileStoreInterface;
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

        $mockDb = $this->getMockBuilder(RelationalDbInterface::class)->getMock();
        $mockDb->method('isReady')->willReturn(true);

        $mockFileStore = $this->getMockBuilder(FileStoreInterface::class)->getMock();
        $mockFileStore->method('isReady')->willReturn(true);

        $healthCheck = new HealthCheck($mockLog, $mockDb, $mockFileStore);
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

        $mockDb = $this->getMockBuilder(RelationalDbInterface::class)->getMock();
        $mockDb->method('isReady')->willReturn(true);

        $mockFileStore = $this->getMockBuilder(FileStoreInterface::class)->getMock();
        $mockFileStore->method('isReady')->willReturn(true);

        $healthCheck = new HealthCheck($mockLog, $mockDb, $mockFileStore);
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

        $mockDb = $this->getMockBuilder(RelationalDbInterface::class)->getMock();
        $mockDb->method('isReady')->willReturn(true);

        $mockFileStore = $this->getMockBuilder(FileStoreInterface::class)->getMock();
        $mockFileStore->method('isReady')->willReturn(true);

        $healthCheck = new HealthCheck($mockLog, $mockDb, $mockFileStore);
        $this->assertFalse($healthCheck->isApplicationHealthy());
    }

    /**
     * Make sure under golden path everything is healthy
     */
    public function testIsSystemHealthy()
    {
        $mockLog = $this->getMockBuilder(LogInterface::class)->getMock();
        $mockLog->method('getLevelStats')->willReturn([]);

        $mockDb = $this->getMockBuilder(RelationalDbInterface::class)->getMock();
        $mockDb->method('isReady')->willReturn(true);

        $mockFileStore = $this->getMockBuilder(FileStoreInterface::class)->getMock();
        $mockFileStore->method('isReady')->willReturn(true);

        $healthCheck = new HealthCheck($mockLog, $mockDb, $mockFileStore);
        $this->assertTrue($healthCheck->isSystemHealthy());
    }

    /**
     * Make sure we fail if the file store cannot be contacted
     */
    public function testIsSystemHealthyFileStoreFail()
    {
        $mockLog = $this->getMockBuilder(LogInterface::class)->getMock();
        $mockLog->method('getLevelStats')->willReturn([]);

        $mockDb = $this->getMockBuilder(RelationalDbInterface::class)->getMock();
        $mockDb->method('isReady')->willReturn(true);

        // Simulate inability to connect
        $mockFileStore = $this->getMockBuilder(FileStoreInterface::class)->getMock();
        $mockFileStore->method('isReady')->willReturn(false);

        $healthCheck = new HealthCheck($mockLog, $mockDb, $mockFileStore);
        $this->assertFalse($healthCheck->isSystemHealthy());
    }

    /**
     * Make sure we fail if the database cannot be connected to
     */
    public function testIsSystemHealthyDatabaseFail()
    {
        $mockLog = $this->getMockBuilder(LogInterface::class)->getMock();
        $mockLog->method('getLevelStats')->willReturn([]);

        $mockDb = $this->getMockBuilder(RelationalDbInterface::class)->getMock();
        $mockDb->method('isReady')->willReturn(false);

        // Simulate inability to connect
        $mockFileStore = $this->getMockBuilder(FileStoreInterface::class)->getMock();
        $mockFileStore->method('isReady')->willReturn(true);

        $healthCheck = new HealthCheck($mockLog, $mockDb, $mockFileStore);
        $this->assertFalse($healthCheck->isSystemHealthy());
    }
}