<?php
/**
 * Test the WorkFlow factory
 */
namespace NetricTest\WorkFlow;

use PHPUnit\Framework\TestCase;
use Netric\WorkFlow\WorkFlow;
use Netric\WorkFlow\WorkFlowFactory;
use NetricTest\Bootstrap;

class WorkFlowFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = Bootstrap::getAccount();
        $sm = $account->getServiceManager();
        $this->assertInstanceOf(
            WorkFlow::class,
            $sm->get(WorkFlowFactory::class)
        );
    }
}
