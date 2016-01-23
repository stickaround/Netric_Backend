<?php
/**
 * Test core netric application class
 */
namespace NetricTest;

use Netric;
use PHPUnit_Framework_TestCase;

class ApplicationTest extends PHPUnit_Framework_TestCase
{
    /**
     * Application object to test
     *
     * @var Netric\Application
     */
    private $application = null;

    /**
     * Name used for test accounts
     */
    const TEST_ACCT_NAME = "unit_test_application";

    protected function setUp()
    {
        $config = new Netric\Config();
        $this->application = new Netric\Application($config);
    }

    public function testGetConfig()
    {
        $this->assertInstanceOf('Netric\Config', $this->application->getConfig());
    }
    
    /**
     * Test getting the current/default account
     */
    public function testGetAccount()
    {
        $this->assertInstanceOf('Netric\Account', $this->application->getAccount());
    }

    public function testGetAccountsByEmail()
    {
        // TODO: Add this test
    }

    public function testCreateAccount()
    {
        // First cleanup in case we left an account around
        $cleanupAccount = $this->application->getAccount(null, self::TEST_ACCT_NAME);
        if ($cleanupAccount)
            $this->application->deleteAccount(self::TEST_ACCT_NAME);

        // Create a new test account
        $account = $this->application->createAccount(self::TEST_ACCT_NAME, "test@test.com", "password");
        $this->assertTrue($account->getId() > 0);

        // Cleanup
        $this->application->deleteAccount(self::TEST_ACCT_NAME);
    }

    public function testUpdateAccount()
    {
        // TODO: Add this test
    }

    /**
     * Check that we can initialize a new database
     */
    public function testInitDb()
    {
        /*
         * The actual function of creating the database is tested
         * in the application DataMapper tests. All we need to do here
         * is make sure this function works with an existing database
         * since the create can be assumed to be thoroughly tested elsewhere.
         */
        $this->assertTrue($this->application->initDb());
    }

    public function testDeleteAccount()
    {
        // First cleanup in case we left an account around
        $cleanupAccount = $this->application->getAccount(null, self::TEST_ACCT_NAME);
        if ($cleanupAccount)
            $this->application->deleteAccount(self::TEST_ACCT_NAME);

        // Create account
        $account = $this->application->createAccount(self::TEST_ACCT_NAME, "test@test.com", "password");

        // Now delete the account
        $this->assertTrue($this->application->deleteAccount(self::TEST_ACCT_NAME));

        // Make sure we cannot open the account - it should be deleted
        $this->assertNull($this->application->getAccount($account->getId()));
    }
}