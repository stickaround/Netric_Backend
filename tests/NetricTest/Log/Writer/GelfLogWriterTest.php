<?php
namespace NetricTest\Log\Writer;

use Netric\Log\Writer\GelfLogWriter;
use Netric\Log\Writer\LogWriterInterface;

/**
 * @group integration
 */
class GelfLogWriterTest extends AbstractLogWriterTests
{
    /***
     * Construct a gelf log writer
     */
    public function getWriter(): LogWriterInterface
    {
        return new GelfLogWriter('logstash');
    }
}