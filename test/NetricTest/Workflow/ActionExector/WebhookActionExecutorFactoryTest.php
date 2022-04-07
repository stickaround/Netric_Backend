<?php

declare(strict_types=1);

namespace NetricTest\Workflow\ActionExecutor;

use NetricTest\Bootstrap;
use PHPUnit\Framework\TestCase;
use Netric\Entity\ObjType\WorkflowActionEntity;
use Netric\Workflow\ActionExecutor\WebhookActionExecutor;
use Netric\Workflow\ActionExecutor\WebhookActionExecutorFactory;

/**
 * Integration test to make sure the factory works
 *
 * @group integration
 */
class WebhookActionExecutorFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $serviceLocator = Bootstrap::getAccount()->getServiceManager();

        // Simple stub
        $testEntity = $this->createStub(WorkflowActionEntity::class);

        $factory = new WebhookActionExecutorFactory();
        $exector = $factory->create($serviceLocator, $testEntity);
        $this->assertInstanceOf(WebhookActionExecutor::class, $exector);
    }
}
