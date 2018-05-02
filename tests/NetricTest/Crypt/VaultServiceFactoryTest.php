<?php
namespace NetricTest\Crypt;

use PHPUnit\Framework\TestCase;
use Netric\Crypt\VaultServiceFactory;

class VaultServiceFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sl = $account->getServiceManager();
        $this->assertInstanceOf(
            VaultServiceFactory::class,
            $sl->get(VaultServiceFactory::class)
        );
    }
}
