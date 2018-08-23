<?php

namespace NetricTest\EntitySync;

use Netric;
use Netric\EntitySync\DataMapperRdb;
use Netric\EntitySync\DataMapperFactory;

use PHPUnit\Framework\TestCase;

class DataMapperFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();

        $this->assertInstanceOf(
            DataMapperRdb::class,
            $sm->get(DataMapperFactory::class)
        );
    }
}
