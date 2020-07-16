<?php

namespace NetricTest\Application;

use Netric\Application\DataMapperInterface;
use Netric\Account\Account;
use Netric\Application\Application;
use Aereus\Config\ConfigLoader;
use PHPUnit\Framework\TestCase;

abstract class AbstractDataMapperTests extends TestCase
{
    /**
     * Application object to test
     *
     * @var Application
     */
    private $application = null;

    /**
     * Test accounts created that should be deleted
     *
     * @var array
     */
    private $testAccountIds = [];

    public $config = null;

    /**
     * A name we can use for creating test accounts
     */
    const TEST_ACCOUNT_NAME = 'unit_test_account';

    /**
     * A test domaon
     */
    const TEST_EMAIL_DOMAIN = 'unittest.com';

    /**
     * Setup each test
     */
    protected function setUp(): void
    {
        $configLoader = new ConfigLoader();
        $applicationEnvironment = (getenv('APPLICATION_ENV')) ? getenv('APPLICATION_ENV') : "production";

        // Setup the new config
        $this->config = $configLoader->fromFolder(__DIR__ . "/../../../config", $applicationEnvironment);
        $this->application = new Application($this->config);

        // Clean-up the test account in case it was left hanging on a previous failure
        $dataMapper = $this->getDataMapper();
        $account = new Account($this->application);
        if ($dataMapper->getAccountByName(self::TEST_ACCOUNT_NAME, $account)) {
            $dataMapper->deleteAccount($account->getAccountId());
        }
    }

    /**
     * Cleanup after each test
     */
    protected function tearDown(): void
    {
        $dataMapper = $this->getDataMapper();

        foreach ($this->testAccountIds as $accountId) {
            $dataMapper->deleteAccount($accountId);
        }
    }

    /**
     * Get an implementation specific DataMapper
     *
     * @param string $optDbName Optional different name to use for the database
     * @return DataMapperInterface
     */
    abstract protected function getDataMapper($optDbName = null);

    /**
     * This is a cleanup method that we need done manually in the datamapper driver
     *
     * We do not want to expose this in the application datamapper since the
     * application database should NEVER be deleted. So we leave it up to each
     * drive to manually delete or drop a temp/test database.
     *
     * @param string $dbName The name of the database to drop
     */
    abstract protected function deleteDatabase($dbName);

    public function testCreateAccount()
    {
        $dataMapper = $this->getDataMapper();
        $aid = $dataMapper->createAccount(self::TEST_ACCOUNT_NAME);
        $this->testAccountIds[] = $aid;
        $error = ($aid === -1) ? $dataMapper->getLastError()->getMessage() : "";

        // Make sure we did not get a 0 which is failure
        $this->assertNotEquals(0, $aid, $error);

        // Now let's try using the function datamapper::getAccounts()
        $result = $dataMapper->getAccounts();
        $this->assertGreaterThan(0, $result);
    }

    public function testDeleteAccount()
    {
        $dataMapper = $this->getDataMapper();
        $aid = $dataMapper->createAccount(self::TEST_ACCOUNT_NAME);

        $ret = $dataMapper->deleteAccount($aid);

        // Now delete it
        $this->assertTrue($ret);
        $this->assertEquals(0, sizeof($dataMapper->getErrors()));

        // Make sure we cannot open it now
        $account = new Account($this->application);
        $this->assertFalse($dataMapper->getAccountById($aid, $account));

        // Try deleting an account that is not existing
        $ret = $dataMapper->deleteAccount(-123);
        $this->assertFalse($ret);

        // Make sure that an error is logged when deleting an account that is not existing
        $this->assertEquals(1, sizeof($dataMapper->getErrors()));
    }

    public function testAcquireLock()
    {
        $dataMapper = $this->getDataMapper();

        // Create a unit test unique lcok name
        $utestLockName = "utest_app_dm_lock";

        // First clean up any leftover process locks
        $dataMapper->releaseLock($utestLockName);

        // Create a new lock with the default expires
        $this->assertTrue($dataMapper->acquireLock($utestLockName));

        // A second call should return false since it is locked
        $this->assertFalse($dataMapper->acquireLock($utestLockName));
    }

    public function testAcquireLockExpired()
    {
        $dataMapper = $this->getDataMapper();

        // Create a unit test unique lock name
        $utestLockName = "utest_app_dm_lock_expired";

        // Create a new lock with the default expires
        $dataMapper->acquireLock($utestLockName);

        // A second call should be expired and return true
        $this->assertTrue($dataMapper->acquireLock($utestLockName, 0));

        // Cleanup
        $dataMapper->releaseLock($utestLockName);
    }

    public function testReleaseLock()
    {
        $dataMapper = $this->getDataMapper();

        // Create a unit test unique lock name
        $utestLockName = "utest_app_dm_lock";

        // Create then release a process lock
        $dataMapper->acquireLock($utestLockName);
        $dataMapper->releaseLock($utestLockName);

        // We should be able to lock the process again now that it was released
        $this->assertTrue($dataMapper->acquireLock($utestLockName));

        // Cleanup
        $dataMapper->releaseLock($utestLockName);
    }

    public function testExtendLock()
    {
        $dataMapper = $this->getDataMapper();

        // Create a unit test unique lock name
        $utestLockName = "utest_app_dm_lock_extended";

        // Create a new lock with the default expires
        $dataMapper->acquireLock($utestLockName);

        // Make sure that a call to update the executed time to now succeeds to renew the lock
        $this->assertTrue($dataMapper->extendLock($utestLockName));

        // Cleanup
        $dataMapper->releaseLock($utestLockName);
    }

    public function testSetAccountUserEmail()
    {
        $dataMapper = $this->getDataMapper();
        $aid = $dataMapper->createAccount(self::TEST_ACCOUNT_NAME);
        $this->testAccountIds[] = $aid;

        // Test creating an account user email
        $ret = $dataMapper->setAccountUserEmail(
            $aid,
            self::TEST_ACCOUNT_NAME,
            'unitest@' . self::TEST_EMAIL_DOMAIN
        );
        $this->assertTrue($ret);

        // Now let's try retrieving the the user email using the function ::setAccountUserEmail()
        $result = $dataMapper->getAccountsByEmail('unitest@' . self::TEST_EMAIL_DOMAIN);
        $this->assertEquals($result[0]["account"], self::TEST_ACCOUNT_NAME);
        $this->assertEquals($result[0]["username"], self::TEST_ACCOUNT_NAME);
    }
}
