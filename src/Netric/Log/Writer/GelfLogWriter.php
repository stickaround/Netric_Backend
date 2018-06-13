<?php
namespace Netric\Log\Writer;

use Netric\Log\LogMessage;
use Netric\Config\Config;
use Gelf\Transport\UdpTransport as GelfUdpTransport;
use Gelf\Publisher as GelfPublisher;
use Gelf\Message as GelfMessage;

/**
 * Write logs to a GELF(graylog) compatible server
 */
class GelfLogWriter implements LogWriterInterface
{
    /**
     * Gelf logger instance
     *
     * @var GelfPublisher
     */
    private $gelfPublisher =  null;

    /**
     * Keep track of how many messages we have written
     *
     * @var int
     */
    private $numMessageWritten = 0;

    /**
     * Construct a new greylog writer
     *
     * @param Config $logConfig
     */
    public function __construct(Config $logConfig)
    {
        // We need a transport - default to UDP
        $transport = new GelfUdpTransport($logConfig->server, 12201);

        // While the UDP transport is itself a publisher, we wrap it in a real Publisher for convenience
        // A publisher allows for message validation before transmission
        $this->gelfPublisher = new GelfPublisher();
        $this->gelfPublisher->addTransport($transport);
    }

    /**
     * Write a LogMessage
     *
     * @param LogMessage $logMessage
     * @return void
     */
    public function write(LogMessage $logMessage)
    {
        $message = new GelfMessage();
        $message->setShortMessage($logMessage->getName());
        $message->setLevel($logMessage->getLevelNumber());

        // Either set the full text body, or additional properties for structured data
        if (is_array($logMessage->getBody())) {
            foreach ($logMessage->getBody() as $key => $val) {
                $message->setAdditional($key, $val);
            }
        } elseif (is_string($logMessage->getBody())) {
            $message->setFullMessage($logMessage->getBody());
        }

        // Add additional structured properties
        $message->setAdditional('client_ip', $logMessage->getClientIp());
        $message->setAdditional('application_environment', $logMessage->getApplicationEnvironment());
        $message->setAdditional('application_version', $logMessage->getApplicationVersion());
        $message->setAdditional('application_name', $logMessage->getApplicationName());
        $message->setAdditional('request_route', $logMessage->getRequestPath());
        $message->setAdditional('request_id', $logMessage->getRequestId());

        $this->gelfPublisher->publish($message);
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
