<?php

namespace NetricTest\Controller;

use Netric;
use PHPUnit\Framework\TestCase;
use Netric\Controller\SetupController;
use NetricTest\Bootstrap;

/**
 * Test querying ElasticSearch server
 *
 * Most tests are inherited from IndexTestsAbstract.php.
 * Only define index specific tests here and try to avoid name collision with the tests
 * in the parent class. For the most part, the parent class tests all public functions
 * so private functions should be tested below.
 *
 * @group integration
 */
class SetupControllerTest extends TestCase
{
    /**
     * Constructed controller
     *
     * @var SetupController
     */
    private $controller = null;

    /**
     * Array of account names to delete on shutDown
     *
     * @var string[]
     */
    private $accountsToCleanup = [];

    /**
     * Construct the controller
     *
     * @return void
     */
    protected function setUp(): void
    {
        $account = Bootstrap::getAccount();
        $this->controller = new SetupController($account->getApplication(), $account);
        $this->controller->testMode = true;
    }

    /**
     * Cleanup after each test
     */
    protected function tearDown(): void
    {
        $account = Bootstrap::getAccount();
        foreach ($this->accountsToCleanup as $tempAccountName) {
            $account->getApplication()->deleteAccount($tempAccountName);
        }
    }

    /**
     * Test automatic pagination
     */
    public function testTest()
    {
        $con = $this->controller;
        $request = $con->getRequest();
        // Queue to run the first script which does not really do anything
        $request->setParam("script", "update/once/005/001/001.php");
        $ret = $con->consoleRunAction();
        // If the return code is 0, then it executed successfully
        $this->assertEquals(0, $ret->getReturnCode());
    }

    /**
     * Make sure the latest version gets returned when queried
     *
     * @return int
     */
    public function testGetVersionAction()
    {
        $ret = $this->controller->getVersionAction();
        $this->assertGreaterThan(0, $ret);
    }

    /**
     * Test creating a new account
     *
     * @return void
     */
    public function testPostCreateAccountAction()
    {
        $tempAccountName = 'testpostcreateaccount';

        // Queue for cleanup
        $this->accountsToCleanup[] = $tempAccountName;

        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setBody(json_encode([
            'account_name' => $tempAccountName,
            'username' => 'test2',
            'email' => 'test2@netric.com',
            'password' => 'PassRandNeverLogin!',
        ]));

        $response = $this->controller->postCreateAccountAction();
        $output = $response->getOutputBuffer();

        // Make sure the accounts
        $this->assertNotEmpty($output['account_id']);
        $this->assertEquals($tempAccountName, $output['name']);
    }

    /**
     * Test creating a new account
     *
     * @return void
     */
    public function testPostCreateAccountActionDuplicateName()
    {
        // This is the same as the account created on bootstrap
        $tempAccountName = 'autotest';

        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setBody(json_encode([
            'account_name' => $tempAccountName,
            'username' => 'test2@netric.com',
            'password' => 'PassRandNeverLogin!',
        ]));

        $response = $this->controller->postCreateAccountAction();
        $output = $response->getOutputBuffer();

        // Queue for cleanup
        $this->accountsToCleanup[] = $output['name'];

        // Make sure the accounts
        $this->assertNotEmpty($output['account_id']);
        $this->assertNotEquals('local', $output['name']);
    }
}
