<?php
namespace NetricTest\PaymentGateway;

use PHPUnit\Framework\TestCase;

/**
 * Integration test against authorize.net
 *
 * @group integration
 */
class AuthDotNetGatewayTODO extends TestCase
{
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
        $this->markTestIncomplete("Working on this");
    }
}