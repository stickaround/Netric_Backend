<?php
/**
 * Test the WorkFlowManager factory
 */
namespace NetricTest\WorkFlow\DataMapper;

use PHPUnit\Framework\TestCase;

class WorkFlowManagerFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();
        $this->assertInstanceOf(
            'Netric\WorkFlow\WorkFlowManager',
            $sm->get('Netric\WorkFlow\WorkFlowManager')
        );
    }
}