<?php

namespace NetricTest\EntitySync;

use Netric;

use PHPUnit\Framework\TestCase;

class DataMapperFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();

        $this->assertInstanceOf(
            'Netric\EntitySync\DataMapperPgsql',
            $sm->get('EntitySync_DataMapper')
        );

        $this->assertInstanceOf(
            'Netric\EntitySync\DataMapperPgsql',
            $sm->get('Netric\EntitySync\DataMapper')
        );
    }
}