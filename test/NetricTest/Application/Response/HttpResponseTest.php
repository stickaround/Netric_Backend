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

    public function testCached()
    {
        $lastModified = new \DateTime();
        $request = new HttpRequest();
        $request->setParam('HTTP_IF_MODIFIED_SINCE', gmdate('r', $lastModified->getTimestamp()));

        $response = new HttpResponse($request);
        $response->setLastModified($lastModified);
        $response->setCacheable('test');

        // Call print output and make sure the header was set to 301
        $response->printOutput();
        $this->assertEquals(HttpResponse::STATUS_CODE_NOT_MODIFIED, $response->getReturnCode());
    }

    public function testCachedButContentNewer()
    {
        $request = new HttpRequest();
        $request->setParam('HTTP_IF_MODIFIED_SINCE', gmdate('r', strtotime('yesterday')));

        $response = new HttpResponse($request);
        // Modified well after HTTP_IF_MODIFIED_SINCE
        $lastModified = new \DateTime();
        $response->setLastModified($lastModified);
        $response->setCacheable('test');

        // Call print output should return 200 since the request was modified after the cached date
        $response->printOutput();
        $this->assertEquals(HttpResponse::STATUS_CODE_OK, $response->getReturnCode());
    }

    /**
     * Test streaming only part of a file for resumable downloads
     */
    public function testStreamContentRange()
    {
        $fileToStream = __DIR__ . "/fixtures/streamtest.txt";
        $fileStream = fopen($fileToStream, 'r');

        $request = new HttpRequest();

        $response = new HttpResponse($request);
        $response->setStream($fileStream);
        $response->setContentLength(filesize($fileToStream));
        $response->suppressOutput(true); // Just buffer output rather than print

        // Try reading only the first 13 bytes

        $start = 0;
        $end = 13;
        $request->setParam('HTTP_RANGE', "bytes=$start-$end");

        $response->stream();
        $headers = $response->getHeaders();
        $this->assertEquals("bytes $start-$end/" . filesize($fileToStream), $headers['Content-Range']);
        $this->assertEquals(stream_get_contents($fileStream, $end, $start), $response->getOutputBuffer());

        // Try reading only the last 10 bytes
        $start = filesize($fileToStream) - 10;
        $end = filesize($fileToStream);
        $request->setParam('HTTP_RANGE', "bytes=$start-$end");
        $response->stream();
        $this->assertEquals(stream_get_contents($fileStream, $end, $start), $response->getOutputBuffer());
    }
}
