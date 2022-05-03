<?php

declare(strict_types=1);

namespace NetricTest\Workflow\ActionExecutor;

use PHPUnit\Framework\TestCase;
use Netric\Entity\ObjType\UserEntity;
use Netric\Entity\EntityInterface;
use Netric\Entity\EntityLoader;
use Netric\Entity\ObjType\WorkflowActionEntity;
use Netric\Workflow\ActionExecutor\ActionExecutorInterface;
use Netric\Workflow\ActionExecutor\WebhookActionExecutor;

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

  /**
   * Mock and stub out the action exector
   */
  protected function setUp(): void
  {
    $this->mockActionEntity = $this->createMock(WorkflowActionEntity::class);
    $this->mockEntityLoader = $this->createMock(EntityLoader::class);

    $this->executor = new WebhookActionExecutor(
      $this->mockEntityLoader,
      $this->mockActionEntity,
      'http://mockhost'
    );
  }

  /**
   * Make sure url exist
  */
  public function testExecute(): void
  {
    // Set the entity action data
    $this->mockActionEntity->method("getData")->willReturn([
      'url' => 'https://mockhost.com/',
    ]);

    // Create a mock test entity, with a mock definition that gets a field
    // This is important because the execute function will make sure the field
    // exists and get the type of data from the field definition
    $testEntity = $this->createMock(EntityInterface::class);
    $testEntity->method('getValue')->with($this->equalTo('url'))->willReturn("https://mockhost.com/");

    // Stub the user to satisfy requirements for call to execute
    $user = $this->createStub(UserEntity::class);

    // Execute
    $this->assertTrue($this->executor->execute($testEntity, $user));
  }

  /*
  * Make sure test return error, if url is empty 
  */
  public function testExecuteFailOnEmptyUrl(): void
  {
    // Set the entity action data
    $this->mockActionEntity->method("getData")->willReturn([
      'url' => '',
    ]);

    // Create a mock test entity
    $testEntity = $this->createMock(EntityInterface::class);

    // Stub the user to satisfy requirements for call to execute
    $user = $this->createStub(UserEntity::class);

    // Execute
    $this->assertFalse($this->executor->execute($testEntity, $user));
    $this->assertNotNull($this->executor->getLastError());
  }

  /*
  * Make sure test return false, if Invalid URL
  */
  public function testExecuteFailOnInvalidUrl(): void
  {
    // Set the entity action data
    $this->mockActionEntity->method("getData")->willReturn([
      'url' => 'http://test.company.com',
    ]);

    // Create a mock test entity
    $testEntity = $this->createMock(EntityInterface::class);

    // Stub the user to satisfy requirements for call to execute
    $user = $this->createStub(UserEntity::class);

    // Execute
    $this->assertFalse($this->executor->execute($testEntity, $user));
  }

}

?>