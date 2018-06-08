<?php
namespace NetricTest\Log\Writer;

use Netric\Log\Writer\PhpErrorLogWriter;
use Netric\Log\Writer\LogWriterInterface;

/**
 * @group integration
 */
class PhpErrorLogWriterTest extends AbstractLogWriterTests
{
    /***
     * Construct a stderr log writer
     */
    public function getWriter(): LogWriterInterface
    {
        $writer = new PhpErrorLogWriter(true);
        // Keep it from pritning to the console
        $writer->suppressOutup();
        return $writer;
    }
}
