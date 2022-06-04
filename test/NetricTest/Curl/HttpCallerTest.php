<?php
namespace NetricTest\Curl;

use Netric\Curl\HttpCaller;
use PHPUnit\Framework\TestCase;

class HttpCallerTest extends TestCase
{
  /*
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
}

?>