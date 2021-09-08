<?php

declare(strict_types=1);

namespace NetricTest\Account\InitData\Sets;

use Netric\Account\InitData\Sets\WorkflowsInitDataFactory;
use Netric\Entity\EntityLoader;
use Netric\Entity\EntityLoaderFactory;
use Netric\Entity\ObjType\WorkflowActionEntity;
use Netric\Entity\ObjType\WorkflowEntity;
use Netric\EntityDefinition\ObjectTypes;
use PHPUnit\Framework\TestCase;

/**
 * @group integration
 */
class WorkflowsInitDataTest extends TestCase
{
    private EntityLoader $entityLoader;
    private string $accountId;

    /**
     * Setup dependencies
     *
     * @return void
     */
    protected function setUp(): void
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $this->accountId = $account->getAccountId();
        $this->entityLoader =  $account->getServiceManager()->get(EntityLoaderFactory::class);
    }

    /**
     * At a basic level, make sure we can run without throwing any exceptions
     */
    public function testSetInitialData()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $dataSet = $account->getServiceManager()->get(WorkflowsInitDataFactory::class);
        $this->assertTrue($dataSet->setInitialData($account));
    }

    /**
     * Make sure we have status closed workflow
     */
    public function testTaskClosedOnStatusChanged()
    {
        // Check for task-close-on-complete
        $workflow = $this->entityLoader->getByUniqueName(
            ObjectTypes::WORKFLOW,
            'task-close-on-complete',
            $this->accountId
        );
        $this->assertInstanceOf(WorkflowEntity::class, $workflow);

        // Check Condition Action
        $actionCheckCond = $this->entityLoader->getByUniqueName(
            ObjectTypes::WORKFLOW_ACTION,
            'task-close-on-complete-condition',
            $this->accountId
        );
        $this->assertInstanceOf(WorkflowActionEntity::class, $actionCheckCond);

        // Wait Action
        $actionWait = $this->entityLoader->getByUniqueName(
            ObjectTypes::WORKFLOW_ACTION,
            'task-close-on-complete-wait',
            $this->accountId
        );
        $this->assertInstanceOf(WorkflowActionEntity::class, $actionWait);
        $this->assertEquals($actionCheckCond->getEntityId(), $actionWait->getValue('parent_action_id'));

        // Update Field Action
        $actionUpdate = $this->entityLoader->getByUniqueName(
            ObjectTypes::WORKFLOW_ACTION,
            'task-close-on-complete-close',
            $this->accountId
        );
        $this->assertInstanceOf(WorkflowActionEntity::class, $actionUpdate);
        $this->assertEquals($actionWait->getEntityId(), $actionUpdate->getValue('parent_action_id'));
    }
}
