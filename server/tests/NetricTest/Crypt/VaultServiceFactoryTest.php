<?php
namespace NetricTest\Crypt;

use PHPUnit\Framework\TestCase;

class VaultServiceFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sl = $account->getServiceManager();
        $this->assertInstanceOf(
            'Netric\Crypt\VaultService',
            $sl->get("Netric/Crypt/VaultService")
        );
    }
}
