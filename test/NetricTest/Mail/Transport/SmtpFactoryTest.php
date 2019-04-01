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
    private $oldSettings = array();

    protected function setUp(): void
{
        $account = Bootstrap::getAccount();
        $settings = $account->getServiceManager()->get(SettingsFactory::class);
        $this->oldSettings = array(
            'smtp_host' => $settings->get("email/smtp_host"),
            'smtp_user' => $settings->get("email/smtp_user"),
            'smtp_password' => $settings->get("email/smtp_password"),
            'smtp_port' => $settings->get("email/smtp_port"),
        );
    }

    protected function tearDown(): void
{
        // Restore cached old settings
        $account = Bootstrap::getAccount();
        $settings = $account->getServiceManager()->get(SettingsFactory::class);
        $settings->set("email/smtp_host", $this->oldSettings['smtp_host']);
        $settings->set("email/smtp_user", $this->oldSettings['smtp_user']);
        $settings->set("email/smtp_password", $this->oldSettings['smtp_password']);
        $settings->set("email/smtp_port", $this->oldSettings['smtp_port']);
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
        $settings->set('email/smtp_host', $testHost);
        $settings->set('email/smtp_port', $testPort);
        $settings->set('email/smtp_user', $testUser);
        $settings->set('email/smtp_password', $testPassword);

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
            array('username'=>$testUser, 'password'=>$testPassword),
            $options->getConnectionConfig()
        );
    }
}
