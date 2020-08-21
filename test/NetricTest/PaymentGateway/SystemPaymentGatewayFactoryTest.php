<?php

namespace NetricTest\PaymentGateway;

use Netric;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\PaymentGateway\SystemPaymentGatewayFactory;
use Netric\PaymentGateway\PaymentGatewayInterface;

/**
 * Make sure we can construct the system payment gateway
 *
 * @group integration
 */
class SystemPaymentGatewayFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = Bootstrap::getAccount();
        $sm = $account->getServiceManager();
        $this->assertInstanceOf(
            PaymentGatewayInterface::class,
            $sm->get(SystemPaymentGatewayFactory::class)
        );
    }
}
