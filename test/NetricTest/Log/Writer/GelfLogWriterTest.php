<?php

namespace NetricTest\Log\Writer;

use Netric\Log\Writer\GelfLogWriter;
use Netric\Log\Writer\LogWriterInterface;
use Aereus\Config\Config;

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
        $config = new Config(['server' => 'logstash', 'port' => 12201, 'skipPublish' => true]);
        return new GelfLogWriter($config);
    }
}
