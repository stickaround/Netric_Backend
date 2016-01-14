<?php
/**
 * Test account setup functions
 */
namespace NetricTest\Application\Setup;

use Netric\Account;
use Netric\Application;
use Netric\Application\Setup\AccountUpdater;
use Netric\Account\AccountIdentityMapper;
use Netric\Application\Setup\Setup;
use PHPUnit_Framework_TestCase;

class AccountUpdaterTest extends PHPUnit_Framework_TestCase
{
    /**
     * Reference to account running for unit tests
     *
     * @var \Netric\Account
     */
    private $account = null;

    /**
     * Test account name
     *
     * @var const
     */
    const TEST_ACCOUNT_NAME = 'ut_acct_updater';

    protected function setUp()
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
    }

    protected function tearDown()
    {
        // Cleanup if there's any left-overs from a failed test
        $application = $this->account->getApplication();
        $accountToDelete = $application->getAccount(null, self::TEST_ACCOUNT_NAME);
        if ($accountToDelete)
            $application->deleteAccount($accountToDelete->getName());
    }

    public function testGetLatestVersion()
    {
        $accountUpdater = new AccountUpdater($this->account);

        // Make sure we got something other than the default
        $this->assertNotEquals("0.0.0", $accountUpdater->getLatestVersion());
    }

    public function testRunOnceUpdates()
    {
        $application = $this->account->getApplication();

        // Create a new test account
        $account = $application->createAccount(self::TEST_ACCOUNT_NAME, "test@test.com", "password");
        $settings = $account->getServiceManager()->get("Netric/Settings/Settings");

        // Run test updates in TestAssets/UpdateScripts which should result in 1.1.1
        $settings->set("system/schema_version", "0.0.0");
        $accountUpdater = new AccountUpdater($account);
        $accountUpdater->setScriptsRootPath(__DIR__ . "/TestAssets/UpdateScripts");
        $accountUpdater->runOnceUpdates();

        // Make sure it all ran
        $this->assertEquals("1.1.1", $settings->get("system/schema_version"));
        // The update script - TestAssets/once/001/001/001.php changes the account name (does not save)
        $this->assertEquals(self::TEST_ACCOUNT_NAME . "-edited", $account->getName());
    }

    public function testRunAlwaysUpdates()
    {
        $application = $this->account->getApplication();

        // Create a new test account
        $account = $application->createAccount(self::TEST_ACCOUNT_NAME, "test@test.com", "password");
        $settings = $account->getServiceManager()->get("Netric/Settings/Settings");

        // Run test updates in TestAssets/UpdateScripts which should result in 1.1.1
        $accountUpdater = new AccountUpdater($account);
        $accountUpdater->setScriptsRootPath(__DIR__ . "/TestAssets/UpdateScripts");
        $accountUpdater->runAlwaysUpdates();

        // An always update will append the account name with -always but not save
        $this->assertEquals(self::TEST_ACCOUNT_NAME . "-always", $account->getName());
    }
}