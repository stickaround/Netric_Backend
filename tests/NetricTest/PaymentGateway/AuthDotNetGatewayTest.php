<?php
namespace NetricTest\PaymentGateway;

use Netric\PaymentGateway\AuthDotNetGateway;
use Netric\PaymentGateway\PaymentMethod\CreditCard;
use Netric\PaymentGateway\ChargeResponse;
use PHPUnit\Framework\TestCase;
use \net\authorize\api\constants\ANetEnvironment;

/**
 * Integration test against authorize.net
 *
 * @group integration
 */
class AuthDotNetGatewayTest extends TestCase
{
    /**
     * Authorize.net sandbox login ID
     */
    const API_LOGIN = '47zCW38But';

    /**
     * Authorize.net sandbox transaction key
     */
    const API_TRANSACTION_KEY = '22hj5fXD3Z2p7Q5W';

    /**
     * Authorize.net sandbox key (not sure yet what this is used for)
     */
    const API_KEY = 'Simon';

    /**
     * Authorize.net sandbox endpoint used to simulate requests
     */
    const AUTH_NET_TEST_URL = ANetEnvironment::SANDBOX;

    /**
     * Payment gateway to test
     *
     * @var AuthDotNetGateway
     */
    private $gateway = null;

    /**
     * Setup authorize.net with test account
     */
    protected function setUp()
    {
        $this->gateway = new AuthDotNetGateway(
            self::API_LOGIN,
            self::API_TRANSACTION_KEY,
            self::AUTH_NET_TEST_URL
        );
    }

    public function testCreatePaymentProfileCreditCard()
    {
        $this->markTestIncomplete('Still working on this');
    }

    public function testCreateProfileBankAccount()
    {
        $this->markTestIncomplete('Still working on this');
    }

    /**
     * Test charging a saved payment profile
     *
     * @return void
     */
    public function testChargeProfile()
    {
        $this->markTestIncomplete('Still working on this');
    }

    /**
     * Test a one-time charge using a credit card
     *
     * @return void
     */
    public function testChargeCard()
    {
        $card = new CreditCard();
        $response = $this->gateway->chargeCard($card, rand(1, 1000));
        $this->assertEquals(ChargeResponse::STATUS_APPROVED, $response->getStatus());
    }
}