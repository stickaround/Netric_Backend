<?php
namespace NetricTest\Crypt;

use PHPUnit\Framework\TestCase;

class VaultServiceTest extends TestCase
{
    public function testGetSecret()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sl = $account->getServiceManager();
        $vaultService = $sl->get("Netric/Crypt/VaultService");
        $this->assertNotEmpty($vaultService->getSecret("My Test Key"));
    }
}
