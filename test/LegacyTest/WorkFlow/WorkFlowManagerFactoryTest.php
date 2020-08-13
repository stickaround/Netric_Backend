<?php

/**
 * Test the WorkFlowManager factory
 */

namespace NetricTest\WorkFlow\DataMapper;

use PHPUnit\Framework\TestCase;
use Netric\WorkFlow\WorkFlowManager;
use Netric\WorkFlow\WorkFlowManagerFactory;
use NetricTest\Bootstrap;

/**
 * @group integration
 */
class WorkFlowManagerFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = Bootstrap::getAccount();
        $sm = $account->getServiceManager();
        $this->assertInstanceOf(
            WorkFlowManager::class,
            $sm->get(WorkFlowManagerFactory::class)
        );
    }
}
