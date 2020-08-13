<?php

declare(strict_types=1);

namespace NetricTest\Workflow\ActionExecutor;

use NetricTest\Bootstrap;
use PHPUnit\Framework\TestCase;
use Netric\Entity\ObjType\WorkflowActionEntity;
use Netric\Workflow\ActionExecutor\UpdateFieldActionExecutor;
use Netric\Workflow\ActionExecutor\UpdateFieldActionExecutorFactory;

/**
 * Integration test to make sure the factory works
 * @group integration
 */
class UpdateFieldActionExecutorFactoryTest extends testCase
{
    public function testCreate(): void
    {
        $serviceLocator = Bootstrap::getAccount()->getServiceManager();

        // Simple stub
        $testEntity = $this->createStub(WorkflowActionEntity::class);

        $factory = new UpdateFieldActionExecutorFactory();
        $exector = $factory->create($serviceLocator, $testEntity);
        $this->assertInstanceOf(UpdateFieldActionExecutor::class, $exector);
    }
}
