<?php

declare(strict_types=1);

namespace NetricTest\Workflow\ActionExecutor;

use PHPUnit\Framework\TestCase;
use Netric\Entity\ObjType\UserEntity;
use Netric\Entity\EntityInterface;
use Netric\Entity\EntityLoader;
use Netric\Entity\ObjType\WorkflowActionEntity;
use Netric\WorkerMan\WorkerService;
use Netric\Workflow\ActionExecutor\ActionExecutorInterface;
use Netric\Workflow\ActionExecutor\WebhookActionExecutor;
use Netric\EntityDefinition\EntityDefinition;
use Netric\EntityDefinition\ObjectTypes;


/**
 * Test action executor
 */
class WebhookActionExecutorTest extends TestCase
{
  /**
   * Executor to test (not a mock of course)
   */
  private ActionExecutorInterface $executor;

  /**
   * mock dependencies
   */
  private EntityLoader $mockEntityLoader;
  private WorkflowActionEntity $mockActionEntity;
  protected WorkerService $workflowService;

  /**
   * Mock and stub out the action exector
   */
  protected function setUp(): void
  {
    $this->mockActionEntity = $this->createMock(WorkflowActionEntity::class);
    $this->mockEntityLoader = $this->createMock(EntityLoader::class);
    $this->mockWorkerService = $this->createMock(WorkerService::class);
    $this->executor = new WebhookActionExecutor(
        $this->mockEntityLoader,
        $this->mockActionEntity,
        'http://mockhost',
        $workflowService
    );
  }

  /**
   * Make sure we can update a basic field
  */
  public function testExecute(): void
  {
    // Set the entity action data
    $this->mockActionEntity->method("getData")->willReturn([
      'name' => 'Test',
      'f_active' => true,
      'f_on_create' => true, // Check the email field of the user entity
      'f_on_delete' => true,
      'f_on_update' => true,
      'url' => 'http://localhost:3003',
    ]);

    // Create a mock test entity, with a mock definition that gets a field
    // This is important because the execute function will make sure the field
    // exists and get the type of data from the field definition
    $testEntity = $this->createMock(EntityInterface::class);
    $testEntity->method('getEntityId')->willReturn('UUID-SAVED');
    $testEntity->method('getValue')->with($this->equalTo('url'))->willReturn("http://localhost:3003");

    $mockEntityDefinition = $this->createStub(EntityDefinition::class);
    $mockEntityDefinition->method('getObjType')->willReturn(ObjectTypes::USER);
    $testEntity->method("getDefinition")->willReturn($mockEntityDefinition);

    // Stub the user to satisfy requirements for call to execute
    $user = $this->createMock(UserEntity::class);
    $user->method('getAccountId')->willReturn('UUID-ACCOUNT-ID');

    // Execute
    $this->assertTrue($this->executor->execute($testEntity, $user));
  }

}

?>