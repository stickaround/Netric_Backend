<?php
namespace NetricTest\Log\Writer;

use Netric\Log\Writer\NullLogWriter;
use Netric\Log\Writer\LogWriterInterface;

class NullLogWriterTest extends AbstractLogWriterTests
{
    /***
     * Construct a stderr log writer
     */
    public function getWriter(): LogWriterInterface
    {
        return new NullLogWriter(true);
    }
}
