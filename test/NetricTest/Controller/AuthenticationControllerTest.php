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
    const TEST_USER = 'test_auth';
    const TEST_USER_PASS = 'testpass';
    const TEST_ACCOUNT_ID = '32b05ad3-895e-47f0-bab6-609b22f323fc';

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

    /**
     * Test the authenticating of user using Post
     */
    public function testPostAuthenticateAction()
    {
        $data = [
            'username' => TEST_USER,
            'password' => TEST_USER_PASS,
            'account' => TEST_ACCOUNT_ID
        ];

        // Mock the authentication service which is used to authenticate user
        $this->mockAuthService->method('authenticate')->willReturn(true);

        // Make sure postAuthenticateAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode($data));
        $response = $this->authenticationController->postAuthenticateAction($request);

        // It should only return the id of the default view
        $this->assertEquals([
            'result' => 'SUCCESS',
            'session_token' => true,
            'user_id' => 'blahuser',
            'account_id' => 'blahaccount'
        ], $response->getOutputBuffer());
    }

    /**
     * Test the authenticating of user using Get
     */
    public function testGetAuthenticateAction()
    {
        // Mock the authentication service which is used to authenticate user
        $this->mockAuthService->method('authenticate')->willReturn(true);

        // Make sure getAuthenticateAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setParam('username', TEST_USER);
        $request->setParam('password', TEST_USER_PASS);
        $request->setParam('account', TEST_ACCOUNT_ID);
        $response = $this->authenticationController->getAuthenticateAction($request);

        // It should only return the id of the default view
        $this->assertEquals([
            'result' => 'SUCCESS',
            'session_token' => true,
            'user_id' => 'blahuser',
            'account_id' => 'blahaccount'
        ], $response->getOutputBuffer());
    }

    /**
     * Catch the possible errors being thrown when there is a problem in authenticating a user
     */
    public function testAuthenticateFail()
    {
        // Mock the authentication service which is used to authenticate user
        $this->mockAuthService->method('authenticate')->willReturn(false);
        $this->mockAuthService->method('getFailureReason')->willReturn(AuthenticationService::CREDENTIAL_INVALID);

        // Test if invalid data sent
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['bogus' => 'data']));
        $response = $this->authenticationController->postAuthenticateAction($request);

        // It should only return the id of the default view
        $this->assertEquals([
            'result' => 'FAIL',
            'reason' => 'username, password and account are required fields.'
        ], $response->getOutputBuffer());

        $data = [
            'username' => 'invalid',
            'password' => 'invalid',
            'account' => TEST_ACCOUNT_ID
        ];

        // Test if invalid login and authenticate will return false
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode($data));
        $response = $this->authenticationController->postAuthenticateAction($request);

        // It should only return the id of the default view
        $this->assertEquals([
            'result' => 'FAIL',
            'reason' => AuthenticationService::CREDENTIAL_INVALID
        ], $response->getOutputBuffer());
    }

    /**
     * Test the logging out of the user
     */
    public function testLogout()
    {
        // Make sure getLogoutAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setParam('username', TEST_USER);
        $request->setParam('password', TEST_USER_PASS);
        $request->setParam('account', TEST_ACCOUNT_ID);
        $response = $this->authenticationController->getLogoutAction($request);

        // It should only return the id of the default view
        $this->assertEquals([
            'result' => 'SUCCESS'
        ], $response->getOutputBuffer());
    }

    /**
     * Test the checking in of the user
     */
    public function testCheckin()
    {
        // Make sure getCheckinAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $response = $this->authenticationController->getCheckinAction($request);

        // It should only return the id of the default view
        $this->assertEquals([
            'result' => 'OK'
        ], $response->getOutputBuffer());
    }

    /**
     * Catch the possible errors being thrown when there is a problem in checking in the user
     */
    public function testCheckinFail()
    {
        $account = Bootstrap::getAccount();
        $serviceManager = $account->getServiceManager();

        $mockAuthService = $this->createMock(AuthenticationService::class);
        $mockAuthService->method('getIdentity')->willReturn(null);

        // Create the controller with mocks
        $authenticationController = new AuthenticationController(
            $this->accountContainer,
            $mockAuthService,
            $serviceManager->getApplication()
        );

        // Make sure getCheckinAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);

        $response = $authenticationController->getCheckinAction($request);

        // It should only return the id of the default view
        $this->assertEquals([
            'result' => 'FAIL'
        ], $response->getOutputBuffer());
    }
}
