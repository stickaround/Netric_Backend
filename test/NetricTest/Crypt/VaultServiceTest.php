<?php

namespace NetricTest\Crypt;

use PHPUnit\Framework\TestCase;
use Netric\Crypt\VaultServiceFactory;
use NetricTest\Bootstrap;

/**
 * @group integration
 */
class VaultServiceTest extends TestCase
{
    public function testGetSecret()
    {
        $account = Bootstrap::getAccount();
        $sl = $account->getServiceManager();
        $vaultService = $sl->get(VaultServiceFactory::class);
        $this->assertNotEmpty($vaultService->getSecret("EntityEnc"));
    }
}
