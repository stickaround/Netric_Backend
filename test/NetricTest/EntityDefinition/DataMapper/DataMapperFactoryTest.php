<?php

namespace NetricTest\EntityDefinition\DataMapper;

use Netric;
use Netric\EntityDefinition\DataMapper\EntityDefinitionDataMapperFactory;
use Netric\EntityDefinition\DataMapper\EntityDefinitionRdbDataMapper;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;

class EntityDefinitionDataMapperFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = Bootstrap::getAccount();
        $sm = $account->getServiceManager();

        $this->assertInstanceOf(
            EntityDefinitionRdbDataMapper::class,
            $sm->get(EntityDefinitionDataMapperFactory::class)
        );
    }
}
