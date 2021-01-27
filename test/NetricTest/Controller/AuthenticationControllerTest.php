<?php

/**
 * Test calling the authentication controller
 */

namespace NetricTest\Controller;

use Netric;
use PHPUnit\Framework\TestCase;
use Netric\Request\HttpRequest;
use Netric\Account\Account;
use Netric\Account\AccountContainerInterface;
use Netric\Application\Response\HttpResponse;
use Netric\Authentication\AuthenticationService;
use Netric\Authentication\AuthenticationIdentity;
use Netric\Controller\AuthenticationController;
use Netric\EntityDefinition\ObjectTypes;
use Netric\Authentication\AuthenticationServiceFactory;
use NetricTest\Bootstrap;
use Ramsey\Uuid\Uuid;

/**
 * @group integration
 */
class AuthenticationControllerTest extends TestCase
{
    /**
     * Initialized controller with mock dependencies
     */
    private AuthenticationController $authenticationController;

    /**
     * Dependency mocks
     */
    private AuthenticationService $mockAuthService;    
    private Account $mockAccount;    
    
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
        // Provide identity for mock auth service
        $this->mockAuthService = $this->createMock(AuthenticationService::class);
        $ident = new AuthenticationIdentity('blahaccount', 'blahuser');
        $this->mockAuthService->method('getIdentity')->willReturn($ident);

        // Return mock authenticated account
        $this->mockAccount = $this->createStub(Account::class);
        $this->mockAccount->method('getAccountId')->willReturn(Uuid::uuid4()->toString());

        $this->accountContainer = $this->createMock(AccountContainerInterface::class);
        $this->accountContainer->method('loadById')->willReturn($this->mockAccount);
                
        $account = Bootstrap::getAccount();
        $serviceManager = $account->getServiceManager();

        // Create the controller with mocks
        $this->authenticationController = new AuthenticationController(
            $this->accountContainer,
            $this->mockAuthService,
            $serviceManager->getApplication()
        );

        $this->authenticationController->testMode = true;
    }

    public function testAuthenticate()
    {
        $data = [
            'username' => TEST_USER,
            'password' => TEST_USER_PASS,
            'account' => TEST_ACCOUNT_ID
        ];

        // Mock the authentication service which is used to authenticate user
        $this->mockAuthService->method('authenticate')->willReturn(true);

        // Make sure postSaveAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode($data));
        $response = $this->authenticationController->getAuthenticateAction($request);

        // It should only return the id of the default view
        $this->assertEquals([], $response->getOutputBuffer());
    }

    /*public function testAuthenticateFail()
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
    }*/
}
