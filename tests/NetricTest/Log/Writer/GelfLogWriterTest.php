<?php
namespace NetricTest\Log\Writer;

use Netric\Log\Writer\GelfLogWriter;
use Netric\Log\Writer\LogWriterInterface;
use Netric\Config\Config;

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
        $config = new Config(['server'=>'logstash', 'port'=>12201]);
        return new GelfLogWriter($config);
    }
}
