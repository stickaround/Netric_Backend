<?php

declare(strict_types=1);

namespace NetricTest\Workflow\ActionExecutor;

use NetricTest\Bootstrap;
use PHPUnit\Framework\TestCase;
use Netric\Entity\ObjType\WorkflowActionEntity;
use Netric\Workflow\ActionExecutor\CheckConditionActionExecutor;
use Netric\Workflow\ActionExecutor\CheckConditionActionExecutorFactory;

/**
 * Integration test to make sure the factory works
 *
 * @group integration
 */
class CheckConditionActionExecutorFactoryTest extends testCase
{
    public function testCreate(): void
    {
        $serviceLocator = Bootstrap::getAccount()->getServiceManager();

        // Simple stub
        $testEntity = $this->createStub(WorkflowActionEntity::class);

        $factory = new CheckConditionActionExecutorFactory();
        $exector = $factory->create($serviceLocator, $testEntity);
        $this->assertInstanceOf(CheckConditionActionExecutor::class, $exector);
    }
}
