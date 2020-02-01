<?php
/**
 * Make sure the bin/scripts/update/once/004/001/028.php script works
 */
namespace BinTest\Update\Once;

use Netric\Settings\SettingsFactory;
use Netric\Db\Relational\RelationalDbFactory;
use Netric\Console\BinScript;
use PHPUnit\Framework\TestCase;

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
     * Setup each test
     */
    protected function setUp(): void
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
        $this->scriptPath = __DIR__ . "/../../../../bin/scripts/update/once/004/001/028.php";
    }

    /**
     * Cleanup after a test runs
     */
    protected function tearDown(): void
    {
        $serviceManager = $this->account->getServiceManager();
        $db = $serviceManager->get(RelationalDbFactory::class);

        // Cleanup any test settings
        $db->query("DELETE FROM settings WHERE name = 'email/smtp_test'");
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
        $settings = $serviceManager->get(SettingsFactory::class);
        
        // Create a test setting for email/smtp
        $result = $settings->set("email/smtp_test", "test_smtp_value");
        $this->assertTrue($result);

        $testSetting = $settings->getNoCache("email/smtp_test");
        $this->assertEquals($testSetting, "test_smtp_value");

        $binScript = new BinScript($this->account->getApplication(), $this->account);
        $this->assertTrue($binScript->run($this->scriptPath));

        // Test that all email/smtp* settings should be deleted already
        $testSetting = $settings->getNoCache("email/smtp_test");
        $this->assertNull($testSetting);
    }
}