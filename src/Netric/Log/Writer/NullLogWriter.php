<?php
namespace Netric\Log\Writer;

use Netric\Log\LogMessage;

/**
 * Log writer that does not actually write anywhere but can be used for testing
 * or silencing all logs.
 */
class NullLogWriter implements LogWriterInterface
{
    /**
     * Keep track of how many messages we have written
     *
     * @var int
     */
    private $numMessageWritten = 0;

    /**
     * Write a LogMessage
     *
     * @param LogMessage $logMessage
     * @return void
     */
    public function write(LogMessage $logMessage)
    {
        $this->numMessageWritten++;
    }

    /**
     * Get the number of messages written since instantiation
     *
     * @return int
     */
    public function getNumMessageWritten(): int
    {
        return $this->numMessageWritten;
    }
}
