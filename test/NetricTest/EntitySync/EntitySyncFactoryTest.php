<?php

namespace NetricTest\EntitySync;

use Netric;

use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\EntitySync\EntitySync;
use Netric\EntitySync\EntitySyncFactory;

/**
 * @group integration
 */
class EntitySyncFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = Bootstrap::getAccount();
        $sm = $account->getServiceManager();

        $this->assertInstanceOf(
            EntitySync::class,
            $sm->get(EntitySyncFactory::class)
        );
    }
}
