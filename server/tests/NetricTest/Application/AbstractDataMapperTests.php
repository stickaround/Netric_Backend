<?php
namespace NetricTest\Application;

use Netric\Application\DataMapperInterface;
use Netric\Account\Account;
use Netric\Application\Application;
use Netric\Config;
use PHPUnit_Framework_TestCase;

abstract class AbstractDataMapperTests extends PHPUnit_Framework_TestCase
{
    /**
     * Application object to test
     *
     * @var Netric\Application
     */
    private $application = null;

    /**
     * Test accounts created that should be deleted
     *
     * @var array
     */
    private $testAccountIds = [];

    /**
     * A name we can use for creating test accounts
     */
    const TEST_ACCOUNT_NAME = 'unit_test_account';

    protected function setUp()
    {
        $config = new Config();
        $this->application = new Application($config);

        // Clean-up the test account in case it was left hanging on a previous failure
        $dataMapper = $this->getDataMapper();
        $account = new Account($this->application);
        if ($dataMapper->getAccountByName(self::TEST_ACCOUNT_NAME, $account)) {
            $dataMapper->deleteAccount($account->getId());
        }
    }

    protected function tearDown()
    {
        $dataMapper = $this->getDataMapper();

        foreach ($this->testAccountIds as $accountId)
        {
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
     * This is a cleanup method that we need done mantually in the datamapper driver
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
    }

    public function testDeleteAccount()
    {
        $dataMapper = $this->getDataMapper();
        $aid = $dataMapper->createAccount(self::TEST_ACCOUNT_NAME);

        // Now delete it
        $this->assertTrue($dataMapper->deleteAccount($aid));

        // Make sure we cannot open it now
        $account = new Account($this->application);
        $this->assertFalse($dataMapper->getAccountById($aid, $account));

    }

    /**
     * Make sure we can create an application database
     */
    public function testCreateDatabase()
    {
        $dataMapper = $this->getDataMapper("testsystemdb");
        $this->assertTrue($dataMapper->createDatabase());

        // Cleanup
        $this->deleteDatabase("testsystemdb");
    }

    /**
     * If we call create database on an existing database, it should return true
     */
    public function testCreateDatabase_Existing()
    {
        $dataMapper = $this->getDataMapper();
        $this->assertTrue($dataMapper->createDatabase());
    }
}