<?php
namespace NetricTest\Crypt;

use PHPUnit\Framework\TestCase;
use Netric\Crypt\VaultServiceFactory;

class VaultServiceTest extends TestCase
{
    public function testGetSecret()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sl = $account->getServiceManager();
        $vaultService = $sl->get(VaultServiceFactory::class);
        $this->assertNotEmpty($vaultService->getSecret("My Test Key"));
    }
}
