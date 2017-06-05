<?php

namespace NetricTest;

use Netric;

use PHPUnit\Framework\TestCase;

class EntityDefinitionLoaderFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();

        $this->assertInstanceOf(
            'Netric\EntityDefinitionLoader',
            $sm->get('EntityDefinitionLoader')
        );

        $this->assertInstanceOf(
            'Netric\EntityDefinitionLoader',
            $sm->get('Netric\EntityDefinitionLoader')
        );
    }
}