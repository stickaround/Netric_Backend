<?php
namespace Netric\Log\Writer;

use Netric\Log\LogMessage;

/**
 * Common interface that all log writers must implement
 */
interface LogWriterInterface
{
    /**
     * Write a LogMessage
     *
     * @param LogMessage $logMessage
     * @return void
     */
    public function write(LogMessage $logMessage);

    /**
     * Get the number of messages written since instantiation
     *
     * @return int
     */
    public function getNumMessageWritten(): int;
}
