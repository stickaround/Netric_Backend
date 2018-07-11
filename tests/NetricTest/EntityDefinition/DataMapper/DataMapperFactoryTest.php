<?php

namespace NetricTest\EntityDefinition\DataMapper;

use Netric;
use Netric\EntityDefinition\DataMapper\DataMapperFactory;
use Netric\EntityDefinition\DataMapper\EntityDefinitionRdbDataMapper;
use PHPUnit\Framework\TestCase;

class DataMapperFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();

        $this->assertInstanceOf(
            EntityDefinitionRdbDataMapper::class,
            $sm->get(DataMapperFactory::class)
        );
    }
}
