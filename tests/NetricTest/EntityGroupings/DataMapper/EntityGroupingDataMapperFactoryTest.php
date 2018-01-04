<?php
namespace NetricTest\EntityGroupings\DataMapper;

use Netric\EntityGroupings\DataMapper\EntityGroupingDataMapperInterface;
use PHPUnit\Framework\TestCase;

class DataMapperFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();

        $this->assertInstanceOf(
            EntityGroupingDataMapperInterface::class,
            $sm->get('Netric\EntityGroupings\DataMapper\EntityGroupingDataMapper')
        );
    }
}