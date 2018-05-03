<?php
namespace NetricTest\Crypt;

use PHPUnit\Framework\TestCase;
use Netric\Crypt\VaultServiceFactory;
use Netric\Crypt\VaultService;

class VaultServiceFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sl = $account->getServiceManager();
        $this->assertInstanceOf(
            VaultService::class,
            $sl->get(VaultServiceFactory::class)
        );
    }
}
