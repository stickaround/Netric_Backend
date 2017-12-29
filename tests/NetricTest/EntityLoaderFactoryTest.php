<?php

namespace NetricTest;

use Netric;

use PHPUnit\Framework\TestCase;

class EntityLoaderFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();

        $this->assertInstanceOf(
            'Netric\EntityLoader',
            $sm->get('EntityLoader')
        );

        $this->assertInstanceOf(
            'Netric\EntityLoader',
            $sm->get('Netric\EntityLoader')
        );
    }
}