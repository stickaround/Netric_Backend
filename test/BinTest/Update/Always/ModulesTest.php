<?php
/**
 * Make sure the bin/scripts/update/always/04-modules.php script works
 */
namespace BinTest\Update\Always;

use Netric\Console\BinScript;
use PHPUnit\Framework\TestCase;

use Netric\Account\Module\ModuleServiceFactory;
use NetricTest\Bootstrap;

class ModulesTest extends TestCase
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
        $this->account = Bootstrap::getAccount();
        $this->scriptPath = __DIR__ . "/../../../../bin/scripts/update/always/04-modules.php";

        // Get the service manager of the current user
        $this->serviceManager = $this->account->getServiceManager();
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
        $binScript = new BinScript($this->account->getApplication(), $this->account);
        $this->assertTrue($binScript->run($this->scriptPath));

        // Get the module service
        $moduleService = $this->serviceManager->get(ModuleServiceFactory::class);

        $module = $moduleService->getByName('settings');

        // Check if sort_order of settings is equal to 11
        $this->assertEquals($module->getSortOrder(), 11);
    }
}
