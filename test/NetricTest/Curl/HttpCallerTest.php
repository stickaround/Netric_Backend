<?php
namespace NetricTest\Curl;

use Netric\Curl\HttpCaller;
use PHPUnit\Framework\TestCase;

class HttpCallerTest extends TestCase
{
  /**
   * Check valid Url return 200 status code 
   * And Invalid Url return 0 status code
   */
  public function testGet(){
    $httpCaller = new HttpCaller();
    
    // Valid Url
    $httpCaller->get('https://www.netric.com');
    $httpStatusCode = $httpCaller->getInfo(CURLINFO_HTTP_CODE);
    $this->assertEquals($httpStatusCode, 200);

    // Invalid Url
    $httpCaller->get('http://test.example.com');
    $httpStatusCode = $httpCaller->getInfo(CURLINFO_HTTP_CODE);
    $this->assertEquals($httpStatusCode, 0);
  }

  /**
   * Curl handler Exec
   */
  public function testPost(){
    $httpCaller = new HttpCaller();

    // Valid Url
    $httpCaller->post('https://www.netric.com');
    $httpStatusCode = $httpCaller->getInfo(CURLINFO_HTTP_CODE);
    $this->assertEquals($httpStatusCode, 200);

    // Invalid Url
    $httpCaller->post('http://test.example.com');
    $httpStatusCode = $httpCaller->getInfo(CURLINFO_HTTP_CODE);
    $this->assertEquals($httpStatusCode, 0);

  }

  /**
  * Check Valid url return false error
  * Invalid url return true error
  */
  public function testGetError(){
    $httpCaller = new HttpCaller();

    // Valid Url
    $httpCaller->get('https://www.netric.com');
    $error = $httpCaller->getError();
    $this->assertFalse($error, 200);

    // Invalid Url
    $httpCaller->get('http://test.example.com');
    $error = $httpCaller->getError();
    $this->assertTrue($error);

  }

  /**
  * Check Errorcode for valid and Invalid Url
  */
  public function testGetErrorCode(){
    $httpCaller = new HttpCaller();

    // Valid Url
    $httpCaller->get('https://www.netric.com');
    $errorCode = $httpCaller->getErrorCode();
    $this->assertEquals($errorCode, 0);

    // Invalid Url
    $httpCaller->get('http://test.example.com');
    $errorCode = $httpCaller->getErrorCode();
    $this->assertEquals($errorCode,6);

  }

  /**
  * Check Error Message for valid and Invalid Url
  */
  public function testGetErrorMessage(){
    $httpCaller = new HttpCaller();

    // Valid Url
    $httpCaller->get('https://www.netric.com');
    $errorMessage = $httpCaller->getErrorMessage();
    $this->assertEquals($errorMessage, null);

    // Invalid Url
    $httpCaller->get('http://test.example.com');
    $errorMessage = $httpCaller->getErrorMessage();
    $this->assertEquals($errorMessage,'Couldn\'t resolve host name: Could not resolve host: test.example.com');
  }

  /**
   * Check close method return null
   */
  public function testClose(){
    $httpCaller = new HttpCaller();
    $close = $httpCaller->close();

    $this->assertNull($close);
  } 
}

?>