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
use Netric\Curl\Curlwrapper;


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
  * @var SAI_CurlStub
  */
  protected $_curlStub;

  const DEFAULT_RESPONSE = true;
  const DEFAULT_ERRORCODE = 'CURLE_COULDNT_RESOLVE_HOST';
  const DEFAULT_ERRORMESSAGE = 'CURLE_COULDNT_RESOLVE_HOST';

  const RETURN_RESPONSE = 1;
  const RETURN_ERRORCODE = 2;
  const RETURN_ERRORMESSAGE = 3;

  /**
   * Mock and stub out the action exector
   */
  protected function setUp(): void
  {
    $this->curlWrapper = new CurlWrapper();
    $this->curlWrapper->setResponse(self::DEFAULT_RESPONSE);
    $this->curlWrapper->setErrorCode(self::DEFAULT_ERRORCODE);

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
    //$curl = $this->_curlStub;

    //Correct Url
    $url = 'https://www.netric.com';

    // Set the entity action data
    $this->mockActionEntity->method("getData")->willReturn([
      'url' => $url,
    ]);
    
    // correct URL should give response
    $expectedResponse = $this->_getResponseFromCurl($url);

    // Create a mock test entity
    $testEntity = $this->createMock(EntityInterface::class);

    // Stub the user to satisfy requirements for call to execute
    $user = $this->createStub(UserEntity::class);

    //Actual responses
    $actualResponse = $this->executor->execute($testEntity, $user);

    $this->assertEquals($expectedResponse, $actualResponse);
  }

  /*
  * Make sure test return error, if url is empty 
  */
 public function testExecuteFailOnEmptyUrl(): void
  {
    //$curl = $this->_curlStub;
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
    $url = 'http://test.company.com';
    // Set the entity action data
    $this->mockActionEntity->method("getData")->willReturn([
      'url' => $url,
    ]);

    $curlErrorResponse = 1;

    // correct URL should give response
    $expectedResponse = $this->_getResponseFromCurl($url);

    // Create a mock test entity
    $testEntity = $this->createMock(EntityInterface::class);

    // Stub the user to satisfy requirements for call to execute
    $user = $this->createStub(UserEntity::class);

    //Actual responses
    $actualResponse = $this->executor->execute($testEntity, $user);

    // Execute Curl Response
    $this->assertEquals($expectedResponse, $curlErrorResponse);

    if($expectedResponse >=  1) $expectedResponse = false;
    //Execute curl rersponse
    $this->assertEquals($expectedResponse, $actualResponse);
  }

  /*
  * Get Curl Response from Curl
  */
  private function _getResponseFromCurl($url = null, $options = null)
  {
      return $this->_getResultFromCurl($url, $options, self::RETURN_RESPONSE);
  }

  /*
  * Get error Response from Curl
  */
  private function _getErrorCodeFromCurl($url = null, $options = null)
  {
      return $this->_getResultFromCurl($url, $options, self::RETURN_ERRORMESSAGE);
  }

  /*
  * Get Result from Curl 
  */
  private function _getResultFromCurl($url, $options, $returnFlag, $opt = 0)
  {
    $curl = $this->curlWrapper;
    $ch = $curl->curl_init($url);

    if ($options != null) {
        $curl->curl_setopt_array($ch, $options);
    }

    ob_start();
    $curl->curl_exec($ch);
    $actualResponse = ob_get_clean();

    $result = null;

    switch($returnFlag)
    {
    case self::RETURN_RESPONSE:
        $result = $actualResponse;
        break;
    case self::RETURN_ERRORCODE:
        $result = $curl->curl_errno($ch);
        break;
    case self::RETURN_ERRORMESSAGE:
        $result = $curl->curl_error($ch);
        break;
    }

    $curl->curl_close($ch);

    return $result;
  }

}

?>