<?php

namespace NetricTest\Entity\DataMapper;

use Netric;
use Netric\Entity\DataMapper\EntityPgsqlDataMapper;
use Netric\Entity\DataMapper\EntityDataMapperFactory;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;

class DataMapperFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = Bootstrap::getAccount();
        $sm = $account->getServiceManager();

        $this->assertInstanceOf(
            EntityPgsqlDataMapper::class,
            $sm->get(EntityDataMapperFactory::class)
        );
    }
}
