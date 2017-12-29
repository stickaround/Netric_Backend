<?php
namespace NetricTest\Mail;

use PHPUnit\Framework\TestCase;

class DeliveryServiceFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sl = $account->getServiceManager();
        $this->assertInstanceOf(
            'Netric\Mail\DeliveryService',
            $sl->get("Netric/Mail/DeliveryService")
        );
    }
}
