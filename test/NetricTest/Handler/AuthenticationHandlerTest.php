<?php

namespace NetricTest\Handler;

use PHPUnit\Framework\TestCase;
use Netric\Account\Account;
use Netric\Account\AccountContainerInterface;
use Netric\Authentication\AuthenticationService;
use Netric\Handler\AuthenticationHandler;
use NetricTest\Bootstrap;

/**
 * @group integration
 */
class AuthenticationHandlerTest extends TestCase
{
    /**
     * Initialized Handler with mock dependencies
     */
    private AuthenticationHandler $authenticationHandler;

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

        // Return mock authenticated account
        $this->mockAccount = $this->createStub(Account::class);
        $this->accountContainer = $this->createMock(AccountContainerInterface::class);
        $this->accountContainer->method('loadById')->willReturn($this->mockAccount);

        $account = Bootstrap::getAccount();
        $serviceManager = $account->getServiceManager();

        // Create the controller with mocks
        $this->authenticationHandler = new AuthenticationHandler(
            $this->accountContainer,
            $this->mockAuthService,
            $serviceManager->getApplication()
        );
    }

    /**
     * Test the checking in of the user
     */
    public function testIsTokenValid()
    {
        // Mock isValid
        $this->mockAuthService->method('isTokenValid')->willReturn(true);

        // Test
        $this->assertTrue($this->mockAuthService->isTokenValid("TEST-VALID"));
    }
}
