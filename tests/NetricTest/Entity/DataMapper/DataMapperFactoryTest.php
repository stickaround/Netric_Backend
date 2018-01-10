<?php

namespace NetricTest\Entity\DataMapper;

use Netric;
use Netric\Entity\DataMapper\EntityRdbDataMapper;
use Netric\Entity\DataMapper\DataMapperFactory;
use PHPUnit\Framework\TestCase;

class DataMapperFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();

        $this->assertInstanceOf(
            EntityRdbDataMapper::class,
            $sm->get('Entity_DataMapper')
        );

        $this->assertInstanceOf(
            EntityRdbDataMapper::class,
            $sm->get(DataMapperFactory::class)
        );
    }
}