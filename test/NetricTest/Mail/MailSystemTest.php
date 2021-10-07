<?php

declare(strict_types=1);

namespace NetricTest\Mail;

use Netric\Account\Account;
use Netric\Account\AccountContainer;
use Netric\Mail\MailSystem;
use Netric\Mail\MailSystemInterface;
use PHPUnit\Framework\TestCase;

/**
 * Test the global mailsystem service
 */
class MailSystemTest extends TestCase
{
    const TEST_ACCOUNT_ID = 'UUID-OF-TEST-ACCOUNT';
    const TEST_ACCOUNT_NAME = 'uniqueaccoutnname';

    private MailSystemInterface $mailSystem;
    private AccountContainer $mockAccountContainer;
    private Account $mockAccount;

    protected function setUp(): void
    {
        // Create a simple mock account
        $mockAccount = $this->createMock(Account::class);
        $mockAccount->method('getName')->willReturn(self::TEST_ACCOUNT_NAME);
        $mockAccount->method('getAccountId')->willReturn(self::TEST_ACCOUNT_ID);
        $this->mockAccount = $mockAccount;

        // Mock the acount container
        $mockAccountContainer = $this->createMock(AccountContainer::class);
        $this->mockAccountContainer = $mockAccountContainer;

        $this->mailSystem = new MailSystem('test.com', $mockAccountContainer);
    }

    public function testGetDefaultDomain()
    {
        $this->mockAccountContainer->method('loadById')->with(self::TEST_ACCOUNT_ID)
            ->will($this->returnValue($this->mockAccount));

        $this->assertEquals(
            self::TEST_ACCOUNT_NAME . ".test.com",
            $this->mailSystem->getDefaultDomain(self::TEST_ACCOUNT_ID)
        );
    }

    public function testGetAccountIdFromDomain()
    {
        $this->mockAccountContainer->method('loadByName')->with(self::TEST_ACCOUNT_NAME)
            ->will($this->returnValue($this->mockAccount));

        $this->assertEquals(
            self::TEST_ACCOUNT_ID,
            $this->mailSystem->getAccountIdFromDomain(self::TEST_ACCOUNT_NAME . ".test.com")
        );
    }

    public function testGetAccountIdFromDomainBadDomain()
    {
        $this->mockAccountContainer->method('loadByname')->with('doesnotexist')
            ->will($this->returnValue(null));

        $this->assertEmpty(
            $this->mailSystem->getAccountIdFromDomain('doesnotexist.test.com')
        );
    }
}
