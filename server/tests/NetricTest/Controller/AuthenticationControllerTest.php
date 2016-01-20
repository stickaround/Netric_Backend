<?php
/**
 * Test calling the authentication controller
 */
namespace NetricTest\Controller;

use Netric;
use PHPUnit_Framework_TestCase;

class AuthenticationControllerTest extends PHPUnit_Framework_TestCase 
{   
    /**
     * Account used for testing
     *
     * @var \Netric\Account
     */
    protected $account = null;

    /**
     * Controller instance used for testing
     *
     * @var \Netric\Controller\AuthenticationController
     */
    protected $controller = null;

    /**
     * Test user
     *
     * @var \Netric\Entity\ObjType\UserEntity
     */
    private $user = null;

    /**
     * Common constants used
     *
     * @cons string
     */
    const TEST_USER = "test_auth";
    const TEST_USER_PASS = "testpass";

    protected function setUp()
    {
        $this->account = \NetricTest\Bootstrap::getAccount();

        // Setup a user for testing
        $loader = $this->account->getServiceManager()->get("EntityLoader");
        $user = $loader->get("user", \Netric\Entity\ObjType\UserEntity::USER_ADMINISTRATOR);
        $this->account->setCurrentUser($user);

        // Create the controller
        $this->controller = new Netric\Controller\AuthenticationController($this->account);
        $this->controller->testMode = true;

        // Setup entity datamapper for handling users
        $dm = $this->account->getServiceManager()->get("Entity_DataMapper");

        // Make sure old test user does not exist
        $query = new \Netric\EntityQuery("user");
        $query->where('name')->equals(self::TEST_USER);
        $index = $this->account->getServiceManager()->get("EntityQuery_Index");
        $res = $index->executeQuery($query);
        for ($i = 0; $i < $res->getTotalNum(); $i++)
        {
            $user = $res->getEntity($i);
            $dm->delete($user, true);
        }

        // Create a test user
        $loader = $this->account->getServiceManager()->get("EntityLoader");
        $user = $loader->create("user");
        $user->setValue("name", self::TEST_USER);
        $user->setValue("password", self::TEST_USER_PASS);
        $user->setValue("active", true);
        $dm->save($user);
        $this->user = $user;
    }

    protected function tearDown()
    {
        if ($this->user)
        {
            $dm = $this->account->getServiceManager()->get("Entity_DataMapper");
            $dm->delete($this->user, true);
        }
    }

    public function testAuthenticate()
    {
        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setParam("username", self::TEST_USER);
        $req->setParam("password", self::TEST_USER_PASS);


        // Try to authenticate
        $ret = $this->controller->authenticate();
        $this->assertEquals("SUCCESS", $ret['result']);
    }

    public function testAuthenticateFail()
    {
        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setParam("username", "notreal");
        $req->setParam("password", "notreal");


        // Try to authenticate
        $ret = $this->controller->authenticate();
        $this->assertEquals("FAIL", $ret['result']);
    }

    public function testLogout()
    {
        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setParam("username", self::TEST_USER);
        $req->setParam("password", self::TEST_USER_PASS);


        // Try to authenticate
        $ret = $this->controller->authenticate();
        $this->assertEquals("SUCCESS", $ret['result']);
        $this->assertNull($this->controller->getRequest()->getParam("Authentication"));
    }

    public function testCheckin()
    {
        // First successfully authenticate and get a session token
        $req = $this->controller->getRequest();
        $req->setParam("username", self::TEST_USER);
        $req->setParam("password", self::TEST_USER_PASS);
        $ret = $this->controller->authenticate();
        $sessionToken = $ret['session_token'];

        // Checkin with the valid token
        $this->controller->getRequest()->setParam("Authentication", $sessionToken);
        $ret = $this->controller->checkin();
        $this->assertEquals("OK", $ret['result']);
    }

    public function testCheckinFail()
    {
        // First successfully authenticate and get a session token
        $req = $this->controller->getRequest();
        $req->setParam("username", self::TEST_USER);
        $req->setParam("password", self::TEST_USER_PASS);
        $ret = $this->controller->authenticate();

        // Clear the identity to force rechecking
        $sm = $this->account->getServiceManager();
        $sm->get("/Netric/Authentication/AuthenticationService")->clearIdentity();

        // Checkin with the valid token
        $this->controller->getRequest()->setParam("Authentication", "BADTOKEN");
        $ret = $this->controller->checkin();
        $this->assertNotEquals("OK", $ret['result']);
    }
}