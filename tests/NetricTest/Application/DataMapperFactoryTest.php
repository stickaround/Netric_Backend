<?php

namespace NetricTest\Application;

use Netric;

use PHPUnit\Framework\TestCase;

class DataMapperFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();

        $this->assertInstanceOf(
            'Netric\Application\DataMapperPgsql',
            $sm->get('Application_DataMapper')
        );

        $this->assertInstanceOf(
            'Netric\Application\DataMapperPgsql',
            $sm->get('Netric\Application\DataMapper')
        );
    }
}