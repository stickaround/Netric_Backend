<?php

namespace BinTest\Update\Once;

use Netric\Console\BinScript;
use PHPUnit\Framework\TestCase;

/**
 * Make sure the bin/scripts/update/once/004/001/009.php script works
 */
class Update004001009Test extends TestCase
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
    protected function setUp()
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
        $this->scriptPath = __DIR__ . "/../../../../bin/scripts/update/once/004/001/009.php";
    }

    /**
     * Make sure the file exists
     *
     * This is more a test of the test to make sure we set the path right
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
    }
}
