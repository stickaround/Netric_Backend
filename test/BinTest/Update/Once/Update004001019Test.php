<?php
/**
 * Make sure the bin/scripts/update/once/004/001/018.php script works
 */
namespace BinTest\Update\Once;

use Netric\Entity\EntityLoaderFactory;
use Netric\Entity\DataMapper\DataMapperFactory as EntityDataMapperFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\EntityQuery;
use Netric\Console\BinScript;
use PHPUnit\Framework\TestCase;
use Netric\EntityDefinition\ObjectTypes;

class Update004001019Test extends TestCase
{
    /**
     * Handle to account
     *
     * @var \Netric\Account\Account
     */
    private $account = null;

    /**
     * Path to the script to test
     *
     * @var string
     */
    private $scriptPath = null;

    /**
     * Test entities that should be cleaned up on tearDown
     *
     * @var EntityInterface[]
     */
    private $testEntities = [];

    /**
     * Test old entities that should be cleaned up on tearDown
     *
     * @var EntityInterface[]
     */
    private $testOldEntities = [];

    /**
     * Setup each test
     */
    protected function setUp(): void
{
        $this->account = \NetricTest\Bootstrap::getAccount();
        $this->scriptPath = __DIR__ . "/../../../../bin/scripts/update/once/004/001/019.php";
    }

    /**
     * Cleanup after a test runs
     */
    protected function tearDown(): void
{
        // Cleanup any test entities
        $loader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        foreach ($this->testEntities as $entity) {
            $loader->delete($entity, true);
        }
    }

    /**
     * Make sure the file exists
     *
     * This is more a test of the test to make sure we set the path right, but why
     * not just use unit tests for our tests? :)
     */
    public function testExists()
    {
        $this->assertTrue(file_exists($this->scriptPath), $this->scriptPath . " not found!");
    }

    /**
     * At a basic level, make sure we can run without throwing any exceptions
     */
    public function testRun()
    {
        $serviceManager = $this->account->getServiceManager();
        $entityLoader = $serviceManager->get(EntityLoaderFactory::class);
        $entityIndex = $serviceManager->get(IndexFactory::class);

        $userDashboardEntity = $entityLoader->create(ObjectTypes::DASHBOARD);
        $userDashboardEntity->setValue("name", "UserActivityDashboard");
        $userDashboardEntity->setValue("uname", "activity");
        $userDashboardEntity->setValue("scope", "user");
        $entityLoader->save($userDashboardEntity);

        // Add this dashboard to the test entities just in case this unit test fail
         $this->testEntities[] = $userDashboardEntity;

        // This script should delete the dashboard activity with the scope user
        $binScript = new BinScript($this->account->getApplication(), $this->account);
        $this->assertTrue($binScript->run($this->scriptPath));

        // Find all dashboard entity with uname "activity" and scope is not system
        $query = new EntityQuery(ObjectTypes::DASHBOARD);
        $query->where("uname")->equals("activity");
        $query->where("scope")->doesNotEqual("system");

        // Get the results
        $results = $entityIndex->executeQuery($query);
        $totalNum = $results->getTotalNum();
        $this->assertEquals($totalNum, 0);

        $systemDashboardEntity = $entityLoader->create(ObjectTypes::DASHBOARD);
        $systemDashboardEntity->setValue("name", "SystemActivityDashboard");
        $systemDashboardEntity->setValue("uname", "activity");
        $systemDashboardEntity->setValue("scope", "system");
        $entityLoader->save($systemDashboardEntity);
        $this->testEntities[] = $systemDashboardEntity;

        // This script should not delete the dashboard activity with the scope system
        $binScript = new BinScript($this->account->getApplication(), $this->account);
        $this->assertTrue($binScript->run($this->scriptPath));

        // Find all dashboard entity with uname "activity" and scope is system
        $query = new EntityQuery(ObjectTypes::DASHBOARD);
        $query->where("uname")->equals("activity");
        $query->where("scope")->equals("system");

        // Get the results
        $results = $entityIndex->executeQuery($query);
        $totalNum = $results->getTotalNum();
        $this->assertGreaterThanOrEqual($totalNum, 1);
    }
}