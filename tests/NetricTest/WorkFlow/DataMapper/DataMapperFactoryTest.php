<?php
/**
 * Test the DataMapper factory for WorkFlow
 */
namespace NetricTest\WorkFlow\DataMapper;

use PHPUnit\Framework\TestCase;
use Netric\WorkFlow\DataMapper\WorkFlowRdbDataMapper;
use Netric\WorkFlow\DataMapper\DataMapperFactory;
use NetricTest\Bootstrap;

class DataMapperFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = Bootstrap::getAccount();
        $sm = $account->getServiceManager();
        $this->assertInstanceOf(
            WorkFlowRdbDataMapper::class,
            $sm->get(DataMapperFactory::class)
        );
    }
}
