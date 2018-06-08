<?php
namespace Netric\Log\Writer;

use Netric\Log\LogMessage;

/**
 * Write logs to whatever php is configured to log errors to (usually stderr)
 */
class PhpErrorLogWriter implements LogWriterInterface
{
    /**
     * Keep track of how many messages we have written
     *
     * @var int
     */
    private $numMessageWritten = 0;

    /**
     * Option to keep from writing to error_log
     *
     * This is mostly used for integration tests to not mess up the console
     *
     * @var bool
     */
    private $suppressOutput = false;
    
    /**
     * Do not actually print to error_log because it will make
     * anything running in a docker console (like unit tests)
     * reall noisy
     */
    public function suppressOutup()
    {
        $this->suppressOutput = true;
    }

    /**
     * Write a LogMessage
     *
     * @param LogMessage $logMessage
     * @return void
     */
    public function write(LogMessage $logMessage)
    {
        $messageToSend = '[' . $logMessage->getLevelNumber() . '] ';
        $messageToSend .= $logMessage->getName() . ': ';
        $messageToSend .= (is_array($logMessage->getBody()))
            ? json_encode($logMessage->getBody())
            : $logMessage->getBody();

        // We may opt to suppress this output which is okay
        // because we really are not here to test if error_log works
        if (!$this->suppressOutput) {
            error_log($messageToSend);
        }

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
