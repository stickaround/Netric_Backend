<?php

/**
 * Test calling the authentication controller
 */

namespace NetricTest\Controller;

use Netric;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Controller\AuthenticationController;
use Netric\Entity\DataMapper\EntityDataMapperFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\EntityQuery\EntityQuery;
use Netric\EntityDefinition\ObjectTypes;
use Netric\Authentication\AuthenticationServiceFactory;

/**
 * @group integration
 */
class AuthenticationControllerTest extends TestCase
{

    /**
     * Account used for testing
     *
     * @var \Netric\Account\Account
     */
    protected $account = null;

    /**
     * Controller instance used for testing
     *
     * @var \AuthenticationController
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
    const TEST_ACCOUNT_ID = "32b05ad3-895e-47f0-bab6-609b22f323fc";

    protected function setUp(): void
    {
        $this->account = Bootstrap::getAccount();

        // Create the controller
        $this->controller = new AuthenticationController($this->account->getApplication(), $this->account);
        $this->controller->testMode = true;

        // Setup entity datamapper for handling users
        $dm = $this->account->getServiceManager()->get(EntityDataMapperFactory::class);

        // Make sure old test user does not exist
        $query = new EntityQuery(ObjectTypes::USER, $this->account->getAccountId());
        $query->where('name')->equals(self::TEST_USER);
        $index = $this->account->getServiceManager()->get(IndexFactory::class);
        $res = $index->executeQuery($query);
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $user = $res->getEntity($i);
            $dm->delete($user, $this->account->getAuthenticatedUser());
        }

        // Create a test user
        $loader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $user = $loader->create(ObjectTypes::USER, $this->account->getAccountId());
        $user->setValue("name", self::TEST_USER);
        $user->setValue("uname", self::TEST_USER);
        $user->setValue("password", self::TEST_USER_PASS);
        $user->setValue("active", true);
        $dm->save($user, $this->account->getSystemUser());
        $this->user = $user;
    }

    protected function tearDown(): void
    {
        if ($this->user) {
            $dm = $this->account->getServiceManager()->get(EntityDataMapperFactory::class);
            $dm->delete($this->user, $this->account->getAuthenticatedUser());
        }
    }

    public function testAuthenticate()
    {
        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setParam("username", self::TEST_USER);
        $req->setParam("password", self::TEST_USER_PASS);
        $req->setParam("account", $this->account->getName());


        // Try to authenticate
        $ret = $this->controller->postAuthenticateAction();
        $this->assertEquals("SUCCESS", $ret['result']);
    }

    public function testAuthenticateFail()
    {
        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setParam("username", "notreal");
        $req->setParam("password", "notreal");
        $req->setParam("account", $this->account->getName());


        // Try to authenticate
        $ret = $this->controller->postAuthenticateAction();
        $this->assertEquals("FAIL", $ret['result']);
    }

    public function testLogout()
    {
        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setParam("username", self::TEST_USER);
        $req->setParam("password", self::TEST_USER_PASS);
        $req->setParam("account", $this->account->getName());


        // Try to authenticate
        $ret = $this->controller->postAuthenticateAction();
        $this->assertEquals("SUCCESS", $ret['result']);
        $this->assertNull($this->controller->getRequest()->getParam("Authentication"));
    }

    public function testCheckin()
    {
        // First successfully authenticate and get a session token
        $req = $this->controller->getRequest();
        $req->setParam("username", self::TEST_USER);
        $req->setParam("password", self::TEST_USER_PASS);
        $req->setParam("account", $this->account->getName());
        $ret = $this->controller->postAuthenticateAction();
        $sessionToken = $ret['session_token'];

        // Checkin with the valid token
        $this->controller->getRequest()->setParam("Authentication", $sessionToken);
        $ret = $this->controller->getCheckinAction();
        $this->assertEquals("OK", $ret['result']);
    }

    public function testCheckinFail()
    {
        // First successfully authenticate and get a session token
        $req = $this->controller->getRequest();
        $req->setParam("username", self::TEST_USER);
        $req->setParam("password", self::TEST_USER_PASS);
        $req->setParam("account", $this->account->getName());
        $ret = $this->controller->postAuthenticateAction();

        // Clear the identity to force rechecking
        $sm = $this->account->getServiceManager();
        $sm->get(AuthenticationServiceFactory::class)->clearAuthorizedCache();

        // Checkin with the valid token
        $this->controller->getRequest()->setParam("Authentication", "BADTOKEN");
        $ret = $this->controller->getCheckinAction();
        $this->assertNotEquals("OK", $ret['result']);
    }
}
