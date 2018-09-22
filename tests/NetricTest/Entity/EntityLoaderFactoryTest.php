<?php

namespace NetricTest\Entity;

use Netric\Entity\EntityLoader;
use Netric\Entity\EntityLoaderFactory;
use NetricTest\Bootstrap;

use PHPUnit\Framework\TestCase;

class EntityLoaderFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = Bootstrap::getAccount();
        $sm = $account->getServiceManager();

        $this->assertInstanceOf(
            EntityLoader::class,
            $sm->get(EntityLoaderFactory::class)
        );
    }
}
