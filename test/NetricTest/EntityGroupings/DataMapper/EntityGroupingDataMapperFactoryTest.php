<?php
namespace NetricTest\EntityGroupings\DataMapper;

use Netric\EntityGroupings\DataMapper\EntityGroupingDataMapperInterface;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\EntityGroupings\DataMapper\EntityGroupingDataMapperFactory;

class DataMapperFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = Bootstrap::getAccount();
        $sm = $account->getServiceManager();

        $this->assertInstanceOf(
            EntityGroupingDataMapperInterface::class,
            $sm->get(EntityGroupingDataMapperFactory::class)
        );
    }
}
