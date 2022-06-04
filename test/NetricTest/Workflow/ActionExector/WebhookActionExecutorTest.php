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
use Netric\Curl\HttpCaller;
use Netric\Error\Error;

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
    $this->httpCaller = new HttpCaller();

    $this->mockActionEntity = $this->createMock(WorkflowActionEntity::class);
    $this->mockEntityLoader = $this->createMock(EntityLoader::class);
    //$this->mockHttpCaller = $this->createMock(HttpCaller::class);

    $this->executor = new WebhookActionExecutor(
      $this->mockEntityLoader,
      $this->mockActionEntity,
      'http://mockhost',
      $this->httpCaller,
    );
  }

  /**
   * Make sure url exist
   */
  public function testExecute(): void
  {
    // Correct Url
    $url = 'https://www.netric.com';

    // Set the entity action data
    $this->mockActionEntity->method("getData")->willReturn([
      'url' => $url,
    ]);
    
    // correct URL should give true response
    $expectedResponse = true;

    // Create a mock test entity
    $testEntity = $this->createMock(EntityInterface::class);

    // Stub the user to satisfy requirements for call to execute
    $user = $this->createStub(UserEntity::class);

    // Actual responses
    $actualResponse = $this->executor->execute($testEntity, $user);
    $this->assertEquals($expectedResponse, $actualResponse);
  }

  /**
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

  /**
   * Make sure test return false, if Invalid URL
   */
  public function testExecuteFailOnInvalidUrl(): void
  {
    $url = 'http://test.company.com';
    // Set the entity action data
    $this->mockActionEntity->method("getData")->willReturn([
      'url' => $url,
    ]);

    // correct URL should give response
    $expectedResponse = false;

    // Create a mock test entity
    $testEntity = $this->createMock(EntityInterface::class);

    // Stub the user to satisfy requirements for call to execute
    $user = $this->createStub(UserEntity::class);

    // Actual responses
    $actualResponse = $this->executor->execute($testEntity, $user);

    // Execute curl rersponse
    $this->assertEquals($expectedResponse, $actualResponse);
  }
}

?>