<?php

declare(strict_types=1);

namespace NetricTest\Workflow\ActionExecutor;

use NetricTest\Bootstrap;
use PHPUnit\Framework\TestCase;
use Netric\Entity\ObjType\WorkflowActionEntity;
use Netric\Workflow\ActionExecutor\WaitConditionActionExecutor;
use Netric\Workflow\ActionExecutor\WaitConditionActionExecutorFactory;

/**
 * Integration test to make sure the factory works
 *
 * @group integration
 */
class WaitConditionActionExecutorFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $serviceLocator = Bootstrap::getAccount()->getServiceManager();

        // Simple stub
        $testEntity = $this->createStub(WorkflowActionEntity::class);

        $factory = new WaitConditionActionExecutorFactory();
        $exector = $factory->create($serviceLocator, $testEntity);
        $this->assertInstanceOf(WaitConditionActionExecutor::class, $exector);
    }
}
