<?php
namespace NetricTest\Log\Writer;

use Netric\Log\Log;
use Netric\Log\LogMessage;
use Netric\Log\Writer\LogWriterInterface;
use PHPUnit\Framework\TestCase;

/**
 * @group integration
 */
abstract class AbstractLogWriterTests extends TestCase
{
    /**
     * Any class that inherits these tests will need to define this function
     */
    abstract public function getWriter(): LogWriterInterface;

    /**
     * Make sure we can write to the log without an exception
     */
    public function testWrite()
    {
        // 'logstash' is set as a host during composer startup
        $writer = $this->getWriter();
        // See if we can write without an exception being thrown
        $message = new LogMessage('unit-test', 'TEST_WRITE');
        $message->setLevelNumber(Log::LOG_ERR);
        $message->setBody('This is a longer part of the log here');
        $writer->write($message);
        // If write succeeds it will increment numMessagesWritten
        $this->assertEquals(1, $writer->getNumMessageWritten());
    }

    /**
     * Test writing a structured (array) message
     */
    public function testWriteArray()
    {
        // 'logstash' is set as a host during composer startup
        $writer = $this->getWriter();
        // See if we can write without an exception being thrown
        $message = new LogMessage('unit-test', 'TEST_WRITE');
        $message->setLevelNumber(Log::LOG_ERR);
        $message->setBody(['firstval'=>1, 'secondval'=>2]);
        $writer->write($message);
        // If write succeeds it will increment numMessagesWritten
        $this->assertEquals(1, $writer->getNumMessageWritten());
    }
}