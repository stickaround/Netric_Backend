<?php

/**
 * Test the Smtp service factory
 */

namespace NetricTest\Mail\Transport;

use Netric\Mail\Transport\SmtpFactory;
use PHPUnit\Framework\TestCase;
use Netric\Settings\SettingsFactory;
use Netric\Mail\Transport\Smtp;
use NetricTest\Bootstrap;

/**
 * @group integration
 */
class SmtpFactoryTest extends TestCase
{
    /**
     * Save old settings so we can revert after the test
     *
     * We are doing this because the factory can return different
     * transport options if the account has manual smtp settings
     *
     * @var array
     */
    private $oldSettings = [];

    protected function setUp(): void
    {
        $account = Bootstrap::getAccount();
        $settings = $account->getServiceManager()->get(SettingsFactory::class);
        $this->oldSettings = [
            'smtp_host' => $settings->get("email/smtp_host", $account->getAccountId()),
            'smtp_user' => $settings->get("email/smtp_user", $account->getAccountId()),
            'smtp_password' => $settings->get("email/smtp_password", $account->getAccountId()),
            'smtp_port' => $settings->get("email/smtp_port", $account->getAccountId()),
        ];
    }

    protected function tearDown(): void
    {
        // Restore cached old settings
        $account = Bootstrap::getAccount();
        $settings = $account->getServiceManager()->get(SettingsFactory::class);
        $settings->set("email/smtp_host", $this->oldSettings['smtp_host'], $account->getAccountId());
        $settings->set("email/smtp_user", $this->oldSettings['smtp_user'], $account->getAccountId());
        $settings->set("email/smtp_password", $this->oldSettings['smtp_password'], $account->getAccountId());
        $settings->set("email/smtp_port", $this->oldSettings['smtp_port'], $account->getAccountId());
    }

    public function testCreateService()
    {
        $account = Bootstrap::getAccount();
        $sm = $account->getServiceManager();
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

        $account = Bootstrap::getAccount();
        $sm = $account->getServiceManager();
        $settings = $sm->get(SettingsFactory::class);
        $settings->set('email/smtp_host', $testHost, $account->getAccountId());
        $settings->set('email/smtp_port', $testPort, $account->getAccountId());
        $settings->set('email/smtp_user', $testUser, $account->getAccountId());
        $settings->set('email/smtp_password', $testPassword, $account->getAccountId());

        $smtpFactory = new SmtpFactory();
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
