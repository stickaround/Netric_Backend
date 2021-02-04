<?php

/**
 * Test the bulk Smtp service factory
 */

namespace NetricTest\Mail\Transport;

use Netric\Mail\Transport\BulkSmtpFactory;
use PHPUnit\Framework\TestCase;
use Netric\Settings\SettingsFactory;
use Netric\Mail\Transport\Smtp;
use Netric\Mail\Transport\SmtpFactory;
use Netric\Account\Account;
use NetricTest\Bootstrap;

/**
 * @group integration
 */
class BulkSmtpFactoryTest extends TestCase
{
    /**
     * Reference to account running for unit tests
     * 
     * @var Account
     */
    private $account;

    protected function setUp(): void
    {
        // Create a new test account to test the settings
        $this->account = Bootstrap::getAccount();
    }

    public function testCreateService()
    {
        $sm = $this->account->getServiceManager();
        $this->assertInstanceOf(
            Smtp::class,
            $sm->get(SmtpFactory::class)
        );
    }

    public function testCreateServiceWithSettings()
    {
        $testHost = 'mail.limited.ltd';
        $testPort = 33;
        $testUser = 'testuser';
        $testPassword = 'password';

        $sm = $this->account->getServiceManager();
        $settings = $sm->get(SettingsFactory::class);
        $settings->set('email/smtp_bulk_host', $testHost, $this->account->getAccountId());
        $settings->set('email/smtp_bulk_port', $testPort, $this->account->getAccountId());
        $settings->set('email/smtp_bulk_user', $testUser, $this->account->getAccountId());
        $settings->set('email/smtp_bulk_password', $testPassword, $this->account->getAccountId());

        $smtpFactory = new BulkSmtpFactory();
        $transport = $smtpFactory->createService($sm);

        $this->assertInstanceOf(
            Smtp::class,
            $transport
        );

        $options = $transport->getOptions();
        $this->assertEquals($testHost, $options->getHost());
        $this->assertEquals($testPort, $options->getPort());
        $this->assertEquals('login', $options->getConnectionClass());
        $this->assertEquals(
            ['username' => $testUser, 'password' => $testPassword],
            $options->getConnectionConfig()
        );
    }
}
