<?php
/**
 * Make sure the bin/scripts/update/once/004/001/028.php script works
 */
namespace BinTest\Update\Once;

use Netric\Console\BinScript;
use PHPUnit\Framework\TestCase;
use Netric\Db\Relational\RelationalDbFactory;

class Update004001028Test extends TestCase
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
    protected function setUp()
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
        $this->scriptPath = __DIR__ . "/../../../../bin/scripts/update/once/004/001/028.php";
    }

    /**
     * Cleanup after a test runs
     */
    protected function tearDown()
    {
        // Cleanup any test entities
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
        $db = $serviceManager->get(RelationalDbFactory::class);

        $binScript = new BinScript($this->account->getApplication(), $this->account);
        $this->assertTrue($binScript->run($this->scriptPath));

        // Test the legacy dashboard widgets if they now exists in the app_widgets table
        $result = $db->query(
            "SELECT * FROM app_widgets WHERE class_name=:class_name",
            ["class_name" => "CWidBookmarks"]
        );

        $this->assertEquals($result->rowCount(), 1);
    }
}