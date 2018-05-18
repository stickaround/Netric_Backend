<?php

namespace NetricTest\EntityDefinition\DataMapper;

use Netric;

use PHPUnit\Framework\TestCase;

class DataMapperFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();

        $this->assertInstanceOf(
            'Netric\EntityDefinition\DataMapper\EntityDefinitionRdbDataMapper',
            $sm->get('EntityDefinition_DataMapper')
        );

        $this->assertInstanceOf(
            'Netric\EntityDefinition\DataMapper\EntityDefinitionRdbDataMapper',
            $sm->get('Netric\EntityDefinition\DataMapper\DataMapper')
        );
    }
}