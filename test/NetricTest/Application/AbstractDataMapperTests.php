<?php

namespace NetricTest\Application;

use Netric\Application\DataMapperInterface;
use Netric\Account\Account;
use Netric\Application\Application;
use Aereus\Config\ConfigLoader;
use DateTime;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

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

    /**
     * Test the creation of a new account
     *
     * @return void
     */
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

    /**
     * Test deleting an account (and all its data)
     *
     * In production this is never called (yet) because we just set the
     * status of the account to expired and archive the data.
     *
     * @return void
     */
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
        $ret = $dataMapper->deleteAccount(Uuid::uuid4()->toString());
        $this->assertFalse($ret);

        // Make sure that an error is logged when deleting an account that is not existing
        $this->assertEquals(1, sizeof($dataMapper->getErrors()));
    }

    /**
     * Make sure we can update the user's email address when it changes
     *
     * @return void
     */
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

    /**
     * Make sure we can save changes to an account after creation
     */
    public function testUpdateAccount(): void
    {
        $dataMapper = $this->getDataMapper();
        $aid = $dataMapper->createAccount(self::TEST_ACCOUNT_NAME);
        $this->testAccountIds[] = $aid;

        // Setup the account
        $mockApplication = $this->createMock(Application::class);
        $account = new Account($mockApplication);
        $accountData = $dataMapper->getAccountById($aid, $account);

        // Now test making changes and saving
        $account->setBillingLastBilled(new DateTime()); // Now
        $account->setBillingNextBill(new DateTime(date("Y-m-d", strtotime("+1 month"))));
        $account->setBillingMonthInterval(3); // Let's try every 90 days

        $this->assertTrue($dataMapper->updateAccount($account));
    }
}
