<?php

/**
 * Test core netric application class
 */

namespace NetricTest\Application;

use Netric;
use Aereus\Config\ConfigLoader;
use Netric\Application\Application;
use PHPUnit\Framework\TestCase;
use Aereus\Config\Config;
use Netric\Account\Account;

class ApplicationTest extends TestCase
{
    /**
     * Application object to test
     *
     * @var Application
     */
    private $application = null;

    /**
     * Name used for test accounts
     */
    const TEST_ACCT_NAME = "unit_test_application";

    protected function setUp(): void
    {
        $configLoader = new ConfigLoader();

        // Setup the new config
        $config = $configLoader->fromFolder(__DIR__ . "/../../../config", 'testing');

        $this->application = new Netric\Application\Application($config);
    }

    public function testGetConfig()
    {
        $this->assertInstanceOf(Config::class, $this->application->getConfig());
    }

    /**
     * Test getting the current/default account
     */
    public function testGetAccount()
    {
        $this->assertInstanceOf(Account::class, $this->application->getAccount());
    }

    public function testGetAccountsByEmail()
    {
        // First cleanup in case we left an account around
        $cleanupAccount = $this->application->getAccount(null, self::TEST_ACCT_NAME);
        if ($cleanupAccount) {
            $this->application->deleteAccount(self::TEST_ACCT_NAME);
        }

        // Create a new test account
        $account = $this->application->createAccount(
            self::TEST_ACCT_NAME,
            "automated_test",
            "automated_test@netric.com",
            'password'
        );

        // Get accounts associated with the amil addres(or username if the same)
        $accounts = $this->application->getAccountsByEmail("test@test.com");

        // Make sure the above user was associated with at least one account
        $this->assertGreaterThanOrEqual(1, count($accounts));

        // Cleanup
        $this->application->deleteAccount(self::TEST_ACCT_NAME);
    }

    public function testCreateAccount()
    {
        // First cleanup in case we left an account around
        $cleanupAccount = $this->application->getAccount(null, self::TEST_ACCT_NAME);
        if ($cleanupAccount) {
            $this->application->deleteAccount(self::TEST_ACCT_NAME);
        }

        // Create a new test account
        $account = $this->application->createAccount(self::TEST_ACCT_NAME, "test@test.com", "password");
        $this->assertTrue($account->getId() > 0);

        // Cleanup
        $this->application->deleteAccount(self::TEST_ACCT_NAME);
    }

    public function testDeleteAccount()
    {
        // First cleanup in case we left an account around
        $cleanupAccount = $this->application->getAccount(null, self::TEST_ACCT_NAME);
        if ($cleanupAccount) {
            $this->application->deleteAccount(self::TEST_ACCT_NAME);
        }

        // Create account
        $account = $this->application->createAccount(self::TEST_ACCT_NAME, "test@test.com", "password");

        // Now delete the account
        $this->assertTrue($this->application->deleteAccount(self::TEST_ACCT_NAME));

        // Make sure we cannot open the account - it should be deleted
        $this->assertNull($this->application->getAccount($account->getId()));
    }

    public function testAcquireLock()
    {
        // Create a unit test unique process name
        $utesrLockName = "utest_app_lock";

        // First clean up any leftover process locks
        $this->application->releaseLock($utesrLockName);

        // Create a new lock with the default expires which should return true
        $this->assertTrue($this->application->acquireLock($utesrLockName));

        // Cleanup
        $this->application->releaseLock($utesrLockName);
    }

    public function testReleaseLock()
    {
        // Create a unit test unique process name
        $utesrLockName = "utest_app_lock";

        // Create then release a process lock
        $this->application->acquireLock($utesrLockName);
        $this->application->releaseLock($utesrLockName);

        // We should be able to lock the process again now that it was released
        $this->assertTrue($this->application->acquireLock($utesrLockName));

        // Cleanup
        $this->application->releaseLock($utesrLockName);
    }
}
