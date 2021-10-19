<?php

declare(strict_types=1);

namespace NetricTest\Workflow;

use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Workflow\WorkflowService;
use Netric\Workflow\WorkflowServiceFactory;

/**
 * @group integration
 */
class WorkflowServiceFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = Bootstrap::getAccount();
        $sm = $account->getServiceManager();
        $this->assertInstanceOf(
            WorkflowService::class,
            $sm->get(WorkflowServiceFactory::class)
        );
    }
}
