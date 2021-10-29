<?php

declare(strict_types=1);

namespace NetricTest\Account\InitData\Sets;

use Netric\Account\Account;
use Netric\Mail\DataMapper\MailDataMapperFactory;
use Netric\Mail\DataMapper\MailDataMapperInterface;
use Netric\Mail\Exception\DomainOwnedByAnotherAccountException;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * Test our mail system DataMapper
 *
 * This class mostly handles writing to the email_* tables
 * to setup routing for the smtp.netric.com service to send
 * incomign emails to netric.
 *
 * @group integration
 */
class MailDataMapperpgsqlTest extends TestCase
{
    /**
     * Domain name we'll be using for our tests
     */
    const TEST_DOMAIN = 'testexample.com';

    /**
     * DataMapper under test
     *
     * @var MailDataMapperInterface
     */
    private MailDataMapperInterface $dataMapper;

    /**
     * Test netric account setup in bootstrap for integration tests
     *
     * @var Account
     */
    private Account $account;

    /**
     * Setup dependencies
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->account = \NetricTest\Bootstrap::getAccount();
        $this->dataMapper = $this->account->getServiceManager()->get(MailDataMapperFactory::class);
    }

    /**
     * Do some cleanup since we do actually make changes to the database
     *
     * @return void
     */
    protected function tearDown(): void
    {
        $this->dataMapper->removeIncomingDomain($this->account->getAccountId(), self::TEST_DOMAIN);
    }

    /**
     * Test adding an example domain - happy path
     *
     * @return void
     */
    public function testAddIncomdingDomain(): void
    {
        $this->assertTrue(
            $this->dataMapper->addIncomingDomain(
                $this->account->getAccountId(),
                self::TEST_DOMAIN
            )
        );
    }

    /**
     * Make sure adding it twice to the same account does not change anything
     *
     * @return void
     */
    public function testAddIncomdingDomainIdempotent(): void
    {
        $this->assertTrue(
            $this->dataMapper->addIncomingDomain(
                $this->account->getAccountId(),
                self::TEST_DOMAIN
            )
        );

        $this->assertTrue(
            $this->dataMapper->addIncomingDomain(
                $this->account->getAccountId(),
                self::TEST_DOMAIN
            )
        );
    }

    /**
     * Test that if another account tries to add the same domain, it will throw an exception
     *
     * @return void
     */
    public function testAddIncomingDomainTwiceException(): void
    {
        $this->dataMapper->addIncomingDomain(
            $this->account->getAccountId(), // The rightful owner
            self::TEST_DOMAIN
        );

        $this->expectException(DomainOwnedByAnotherAccountException::class);

        $this->dataMapper->addIncomingDomain(
            Uuid::uuid4()->toString(), // New account ID trying to add the same domain
            self::TEST_DOMAIN
        );
    }
}
