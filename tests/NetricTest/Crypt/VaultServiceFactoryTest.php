<?php
namespace NetricTest\Crypt;

use PHPUnit\Framework\TestCase;
use Netric\Crypt\VaultServiceFactory;
use Netric\Crypt\VaultService;
use NetricTest\Bootstrap;

class VaultServiceFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = Bootstrap::getAccount();
        $sl = $account->getServiceManager();
        $this->assertInstanceOf(
            VaultService::class,
            $sl->get(VaultServiceFactory::class)
        );
    }
}
