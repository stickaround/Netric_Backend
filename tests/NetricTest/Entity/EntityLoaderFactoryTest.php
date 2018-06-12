<?php

namespace NetricTest\Entity;

use Netric\Entity\EntityLoader;
use Netric\Entity\EntityLoaderFactory;

use PHPUnit\Framework\TestCase;

class EntityLoaderFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();

        $this->assertInstanceOf(
            EntityLoader::class,
            $sm->get(EntityLoaderFactory::class)
        );
    }
}