<?php
namespace NetricTest\Mail;

use PHPUnit\Framework\TestCase;

class SenderServiceFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sl = $account->getServiceManager();
        $this->assertInstanceOf(
            'Netric\Mail\SEnderService',
            $sl->get("Netric/Mail/SenderService")
        );
    }
}
