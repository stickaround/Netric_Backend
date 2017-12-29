<?php
namespace NetricTest\Entity;

use PHPUnit\Framework\TestCase;
use Netric\Entity\EntityMaintainerService;

class EntityMaintainerServiceFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sl = $account->getServiceManager();
        $this->assertInstanceOf(
            EntityMaintainerService::class,
            $sl->get("Netric\Entity\EntityMaintainerService")
        );
    }
}
