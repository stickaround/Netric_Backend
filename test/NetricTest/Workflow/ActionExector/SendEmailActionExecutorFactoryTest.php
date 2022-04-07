<?php

declare(strict_types=1);

namespace NetricTest\Workflow\ActionExecutor;

use NetricTest\Bootstrap;
use PHPUnit\Framework\TestCase;
use Netric\Entity\ObjType\WorkflowActionEntity;
use Netric\Workflow\ActionExecutor\SendEmailActionExecutor;
use Netric\Workflow\ActionExecutor\SendEmailActionExecutorFactory;

/**
 * Integration test to make sure the factory works
 *
 * @group integration
 */
class SendEmailActionExecutorFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $serviceLocator = Bootstrap::getAccount()->getServiceManager();

        // Simple stub
        $testEntity = $this->createStub(WorkflowActionEntity::class);

        $factory = new SendEmailActionExecutorFactory();
        $exector = $factory->create($serviceLocator, $testEntity);
        $this->assertInstanceOf(SendEmailActionExecutor::class, $exector);
    }
}
