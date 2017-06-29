<?php
namespace NetricTest\Application\Response;

use Netric\Request\HttpRequest;
use Netric\Application\Response\HttpResponse;
use PHPUnit\Framework\TestCase;

class HttpResponseTest extends TestCase
{
    public function testSetContentType()
    {
        $request = new HttpRequest();
        $response = new HttpResponse($request);
        $response->setContentType(HttpResponse::TYPE_JSON);
        // Make sure the header was set
        $headers = $response->getHeaders();
        $this->assertEquals(HttpResponse::TYPE_JSON, $headers['Content-Type']);
    }

    public function testSetContentLength()
    {
        $request = new HttpRequest();
        $response = new HttpResponse($request);
        $response->setContentLength(1000);
        // Make sure the header was set
        $headers = $response->getHeaders();
        $this->assertEquals(1000, $headers['Content-Length']);
    }

    public function testCacheable()
    {
        $request = new HttpRequest();
        $response = new HttpResponse($request);
        $response->setCacheable('test_unique_id');
        // Make sure the header was set
        $headers = $response->getHeaders();
        $this->assertEquals('public', $headers['Pragma']);
        $this->assertEquals('test_unique_id', $headers['Etag']);
    }

    public function testSetLastModified()
    {
        $lastModified = new \DateTime();
        $request = new HttpRequest();
        $response = new HttpResponse($request);
        $response->setLastModified($lastModified);
        // Make sure the header was set
        $headers = $response->getHeaders();
        $lastModifiedHeader = $headers['Last-Modified'];
        $this->assertEquals($lastModified->getTimestamp(), strtotime($lastModifiedHeader));
    }
}