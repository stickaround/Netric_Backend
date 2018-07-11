<?php
namespace NetricTest\Log;

use Netric\Log\LogMessage;
use Netric\Log\Log;
use PHPUnit\Framework\TestCase;

class LogMessageTest extends TestCase
{
    public function testGetName()
    {
        $logMessage = new LogMessage('netric_server', 'TEST');
        $this->assertEquals('TEST', $logMessage->getName());
    }

    public function testGetApplicationName()
    {
        $logMessage = new LogMessage('netric_server', 'TEST');
        $this->assertEquals('netric_server', $logMessage->getApplicationName());
    }

    public function testSetAndGetLevelNumber()
    {
        $logMessage = new LogMessage('netric_server', 'TEST');
        $logMessage->setLevelNumber(Log::LOG_ERR);
        $this->assertEquals(Log::LOG_ERR, $logMessage->getLevelNumber());
    }

    public function testGetLevelName()
    {
        $logMessage = new LogMessage('netric_server', 'TEST');
        $logMessage->setLevelNumber(Log::LOG_ERR);
        $this->assertEquals('error', $logMessage->getLevelName());
    }

    public function testSetAndGetClientIp()
    {
        $data = '192.168.1.1';
        $logMessage = new LogMessage('netric_server', 'TEST');
        $logMessage->setClientIp($data);
        $this->assertEquals($data, $logMessage->getClientIp());
    }

    public function testGetAndSetApplicationEnvironment()
    {
        $data = 'testing';
        $logMessage = new LogMessage('netric_server', 'TEST');
        $logMessage->setApplicationEnvironment($data);
        $this->assertEquals($data, $logMessage->getApplicationEnvironment());
    }
    
    public function testGetAndSetApplicationVersion()
    {
        $data = 'v123';
        $logMessage = new LogMessage('netric_server', 'TEST');
        $logMessage->setApplicationVersion($data);
        $this->assertEquals($data, $logMessage->getApplicationVersion());
    }

    public function testGetAndSetRequestPath()
    {
        $data = '/my/path';
        $logMessage = new LogMessage('netric_server', 'TEST');
        $logMessage->setRequestPath($data);
        $this->assertEquals($data, $logMessage->getRequestPath());
    }

    public function testGetAndSetRequestId()
    {
        $data = 'GGHHh';
        $logMessage = new LogMessage('netric_server', 'TEST');
        $logMessage->setRequestId($data);
        $this->assertEquals($data, $logMessage->getRequestId());
    }

    public function testGetAndSetBodyString()
    {
        $data = 'testing';
        $logMessage = new LogMessage('netric_server', 'TEST');
        $logMessage->setBody($data);
        $this->assertEquals($data, $logMessage->getBody());
    }
    
    public function testGetAndSetBodyArray()
    {
        $data = ['key1'=>'one', 'key2'=>'two'];
        $logMessage = new LogMessage('netric_server', 'TEST');
        $logMessage->setBody($data);
        $this->assertEquals($data, $logMessage->getBody());
    }
}
