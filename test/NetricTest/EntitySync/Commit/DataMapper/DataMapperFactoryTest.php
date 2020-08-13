<?php

namespace NetricTest\EntitySync\Commit\DataMapper;

use Netric;
use Netric\EntitySync\Commit\DataMapper\DataMapperRdb;
use Netric\EntitySync\Commit\DataMapper\DataMapperFactory;
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
