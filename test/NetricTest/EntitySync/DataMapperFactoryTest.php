<?php

namespace NetricTest\EntitySync;

use Netric;
use Netric\EntitySync\DataMapperRdb;
use Netric\EntitySync\DataMapperFactory;
use NetricTest\Bootstrap;

use PHPUnit\Framework\TestCase;

/**
 * @group integration
 */
class DataMapperFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = Bootstrap::getAccount();
        $sm = $account->getServiceManager();

        $this->assertInstanceOf(
            DataMapperRdb::class,
            $sm->get(DataMapperFactory::class)
        );
    }
}
